# Déploiement automatique via GitHub Actions

Guide de mise en place et d'exploitation du déploiement continu vers o2switch.

À chaque `push` sur `main`, le site est construit sur un runner GitHub puis déployé
en production. Aucune action manuelle n'est nécessaire.

---

## Principe

L'idée directrice : **tout ce qui est coûteux ou fragile s'exécute sur le runner
GitHub, jamais sur l'hébergement mutualisé.** Les extensions PHP d'o2switch se
réinitialisent lors des mises à jour de build et cassaient régulièrement Composer ;
en construisant ailleurs, le problème disparaît.

Une difficulté à contourner : **o2switch filtre le SSH par adresse IP**, et les
runners GitHub ont des IP imprévisibles. La solution est l'API cPanel
`SshWhitelist`, qui permet d'autoriser une IP à la volée pour la durée du job.

> Une première tentative passait par un webhook HTTPS. Elle a échoué : le pare-feu
> de la plateforme bloque le trafic entrant venant d'IP de datacenter, quel que
> soit le contenu de la requête. La voie SSH, elle, fonctionne.

---

## Prérequis

| Élément | Détail |
|---|---|
| Hébergement | Compte o2switch avec accès SSH activé |
| Dépôt | Le serveur doit pouvoir faire `git fetch` depuis GitHub |
| PHP | Version 8.4 côté serveur **et** côté runner |
| Token API cPanel | À créer, voir étape 1 |
| Clé SSH dédiée | À générer, voir étape 2 |
| Cron | L'ancien déploiement par cron doit être **désactivé**, voir étape 5 |

---

## Mise en place (une seule fois)

### 1. Créer un token API cPanel

Dans cPanel → **Sécurité** → **Jetons d'API** → *Créer*.

Nommez-le par exemple `github-actions-deploy`. **Copiez le token immédiatement :
il n'est affiché qu'une seule fois.**

### 2. Générer une clé SSH dédiée

Une clé distincte de votre clé personnelle, pour pouvoir la révoquer sans perdre
votre propre accès :

```bash
ssh-keygen -t ed25519 -f ~/.ssh/id_ed25519_github_deploy -N "" -C "github-actions-deploy"
```

Puis autoriser la clé publique sur le serveur :

```bash
ssh nayo1552@cornufer.o2switch.net "cat >> ~/.ssh/authorized_keys" < ~/.ssh/id_ed25519_github_deploy.pub
```

Vérifier qu'elle fonctionne :

```bash
ssh -i ~/.ssh/id_ed25519_github_deploy -o IdentitiesOnly=yes nayo1552@cornufer.o2switch.net "whoami"
```

### 3. Enregistrer les secrets GitHub

Dans le dépôt → **Settings** → **Secrets and variables** → **Actions** →
*New repository secret*. Quatre secrets sont nécessaires :

| Nom | Valeur |
|---|---|
| `CPANEL_USERNAME` | `nayo1552` |
| `CPANEL_SERVER` | `cornufer.o2switch.net` |
| `CPANEL_API_TOKEN` | Le token de l'étape 1 |
| `O2SWITCH_SSH_KEY` | Le contenu de la clé **privée** `~/.ssh/id_ed25519_github_deploy`, lignes `BEGIN`/`END` comprises |

### 4. Vérifier la configuration du serveur

Le dépôt doit être en place et le `.env.local` de production renseigné :

```bash
ssh nayo1552@cornufer.o2switch.net
cd ~/repositories/guillaumehurard
git remote -v          # doit pointer vers GitHub
ls -la .env.local      # doit exister, en permissions 600
```

### 5. Désactiver le cron de déploiement

**Indispensable.** Les deux mécanismes ne peuvent pas cohabiter : le cron se
réveille toutes les 10 minutes et lancerait un `git reset --hard` suivi d'un
`composer install` pendant que le workflow transfère `vendor/`, laissant le site
dans un état incohérent.

> ⚠️ **Ne jamais faire `crontab -l | sed ... | crontab -`.** Si la transformation
> échoue, un crontab vide est appliqué et **toutes** les lignes sont perdues, y
> compris celles des autres projets. Construire le fichier, le vérifier, puis
> l'installer.

```bash
crontab -l > ~/crontab.bak.$(date +%Y%m%d%H%M%S)          # sauvegarde d'abord
crontab -l | grep -v 'deploy_guillaumehurard' > ~/crontab.nouveau
echo '# Desactive : deploiement repris par GitHub Actions' >> ~/crontab.nouveau
crontab -l | grep 'deploy_guillaumehurard' | sed 's/^/#/' >> ~/crontab.nouveau

cat ~/crontab.nouveau                                      # VERIFIER avant d'installer
crontab ~/crontab.nouveau
crontab -l                                                 # controler le resultat
```

Le script `~/deploy_guillaumehurard.sh` reste sur le serveur : décommenter la
ligne suffit à revenir en arrière.

### 6. Lancer un premier déploiement

Onglet **Actions** → workflow **Deploiement o2switch** → *Run workflow*.

---

## Au quotidien

Rien à faire : **tout `push` sur `main` déclenche un déploiement.** Le workflow
peut aussi être lancé à la main depuis l'onglet Actions.

Ce qu'il enchaîne :

1. Build sur le runner — `composer install --no-dev` puis `asset-map:compile`
2. Vidage de la liste blanche SSH, puis ajout de l'IP du runner
3. Sauvegarde du `.env.local` de production
4. `git fetch` + `reset --hard origin/main` côté serveur
5. Rsync de `vendor/`, `public/assets/` et `assets/vendor/` uniquement
6. Migrations et reconstruction du cache
7. Health check : `/` et `/en` doivent renvoyer 200, sinon le job échoue
8. Fermeture de l'accès SSH et effacement de la clé, quoi qu'il arrive

Le code source arrive par git, seuls les artefacts non versionnés passent par
rsync. Il n'y a donc **aucun `--delete` à la racine du projet** : le `.env.local`
ne peut pas être détruit par une erreur de configuration du transfert.

---

## Points d'attention

### Votre IP est effacée à chaque déploiement

La liste blanche SSH est plafonnée à **5 entrées** et l'API ne permet pas de
distinguer l'IP d'un runner de celle d'un poste de travail. Le workflow commence
donc par un `remove_all`.

**Conséquence : après chaque déploiement, votre accès SSH manuel ne fonctionne
plus.** Il faut ré-autoriser votre IP dans cPanel → **Sécurité** →
**Autorisation SSH**.

Pensez aussi à vérifier votre IP publique, qui change régulièrement sur une
connexion résidentielle :

```bash
curl -s https://api.ipify.org
```

**Symptôme d'une IP non autorisée :** `Connection timed out` ou
`Connection reset by peer` **pendant l'échange de bannière** — la connexion TCP
s'établit puis est coupée. Réflexe : vérifier son IP avant de chercher ailleurs.

### Délai de propagation

Une suppression met environ **5 minutes** à devenir effective. Il est normal que
le SSH réponde encore juste après un déploiement, puis se coupe quelques minutes
plus tard.

---

## Dépannage

### Le job échoue sur « Ouvre l'acces SSH »

Message `Vous avez atteint la limite d'exceptions autorisées` : la liste blanche
est pleine. Le workflow fait normalement un `remove_all` au préalable — si
l'erreur persiste, vider la liste à la main depuis cPanel.

### Le job échoue sur le health check

Le site ne répond plus 200. Consulter les logs de production :

```bash
tail -50 ~/repositories/guillaumehurard/var/log/prod.log
```

> Les logs de production sont écrits dans un fichier et non sur `php://stderr`,
> qui n'atterrit nulle part de consultable sur ce compte. Ne pas défaire cette
> configuration dans `config/packages/monolog.yaml`, sans quoi tout diagnostic
> devient impossible.

### Retour arrière complet

Réactiver le cron et le site repart sur l'ancien mécanisme :

```bash
crontab -l > ~/crontab.bak.$(date +%Y%m%d%H%M%S)
crontab -l | sed 's/^#\(\*\/10.*deploy_guillaumehurard\)/\1/' > ~/crontab.nouveau
cat ~/crontab.nouveau     # VERIFIER
crontab ~/crontab.nouveau
```

Puis désactiver le workflow depuis l'onglet Actions de GitHub.

---

## Fichiers concernés

| Fichier | Rôle |
|---|---|
| `.github/workflows/deploiement.yml` | Le workflow lui-même |
| `~/deploy_guillaumehurard.sh` | Ancien script cron, conservé en secours |
| `~/.env.local.guillaumehurard.sauvegarde` | Sauvegarde rafraîchie à chaque déploiement |
