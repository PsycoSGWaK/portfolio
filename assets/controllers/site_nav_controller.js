import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['hero'];

    connect() {
        if (!this.hasHeroTarget) {
            return;
        }

        this.observer = new IntersectionObserver(
            ([entry]) => {
                document.body.classList.toggle('has-scrolled-past-hero', !entry.isIntersecting);
            },
            { threshold: 0.1 },
        );
        this.observer.observe(this.heroTarget);
    }

    disconnect() {
        this.observer?.disconnect();
    }
}
