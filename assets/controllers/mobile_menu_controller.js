import { Controller } from '@hotwired/stimulus';

/*
 * Toggles the mobile navigation (header, < md breakpoint).
 *
 * <header data-controller="mobile-menu">
 *   <button data-action="mobile-menu#toggle" data-mobile-menu-target="button">…</button>
 *   <nav data-mobile-menu-target="menu" class="hidden">…</nav>
 */
export default class extends Controller {
    static targets = ['menu', 'button', 'openIcon', 'closeIcon'];

    toggle() {
        this.#setOpen(this.menuTarget.classList.contains('hidden'));
    }

    close() {
        this.#setOpen(false);
    }

    #setOpen(open) {
        this.menuTarget.classList.toggle('hidden', !open);
        this.buttonTarget.setAttribute('aria-expanded', String(open));
        this.openIconTarget.classList.toggle('hidden', open);
        this.closeIconTarget.classList.toggle('hidden', !open);
    }
}
