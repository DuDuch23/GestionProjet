import { Controller } from '@hotwired/stimulus';

/*
 * Left sidebar: always visible from the md breakpoint, slide-in drawer below it.
 *
 * <body data-controller="sidebar">
 *   <button data-action="sidebar#open" data-sidebar-target="toggle">…</button>
 *   <div data-sidebar-target="backdrop" data-action="click->sidebar#close" class="hidden"></div>
 *   <aside data-sidebar-target="panel" class="-translate-x-full invisible md:visible md:translate-x-0">
 *     <button data-action="sidebar#close">…</button>
 *   </aside>
 */
export default class extends Controller {
    static targets = ['panel', 'backdrop', 'toggle'];
    static closedClasses = ['-translate-x-full', 'invisible'];

    connect() {
        this.onKeydown = (event) => {
            if (event.key === 'Escape') this.close();
        };
        document.addEventListener('keydown', this.onKeydown);
    }

    disconnect() {
        document.removeEventListener('keydown', this.onKeydown);
        document.body.classList.remove('overflow-hidden');
    }

    open() {
        this.#setOpen(true);
        this.panelTarget.querySelector('a, button')?.focus();
    }

    close() {
        if (!this.backdropTarget.classList.contains('hidden')) {
            this.#setOpen(false);
            this.toggleTarget.focus();
        }
    }

    #setOpen(open) {
        this.constructor.closedClasses.forEach((c) => this.panelTarget.classList.toggle(c, !open));
        this.backdropTarget.classList.toggle('hidden', !open);
        this.toggleTarget.setAttribute('aria-expanded', String(open));
        document.body.classList.toggle('overflow-hidden', open); // no page scroll behind the drawer
    }
}
