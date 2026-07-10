import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['track'];

    prev() {
        this.trackTarget.scrollBy({ left: -this.trackTarget.clientWidth, behavior: 'smooth' });
    }

    next() {
        this.trackTarget.scrollBy({ left: this.trackTarget.clientWidth, behavior: 'smooth' });
    }
}
