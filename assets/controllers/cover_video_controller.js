import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        this.mq = window.matchMedia('(prefers-reduced-motion: reduce)');
        this.sync = this.sync.bind(this);
        this.mq.addEventListener('change', this.sync);
        this.sync();
    }

    disconnect() {
        this.mq.removeEventListener('change', this.sync);
    }

    sync() {
        if (this.mq.matches) {
            this.element.pause();
        } else {
            this.element.play().catch(() => {});
        }
    }
}
