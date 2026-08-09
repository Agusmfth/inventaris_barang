import './bootstrap';
import '@hotwired/turbo';
import * as bootstrap from 'bootstrap';
import { createIcons, LayoutDashboard, Shapes, MapPin, Landmark, Package, QrCode, ScanQrCode, ScanLine, Camera, CameraOff, SwitchCamera, Focus, Keyboard, Download, ArrowLeftRight, ClipboardList, Wrench, Trash2, FileChartColumn, Users, Settings, LogOut, Menu, Bell, CircleCheck, CircleAlert, CircleX, TriangleAlert, Repeat2, CalendarDays, Plus, Eye, EyeOff, School, ShieldCheck, LockKeyhole, User, X, EllipsisVertical, Pencil, Power, Search, RotateCcw, Tags, Printer, ImagePlus, Save, ArrowLeft, ArrowRight, ArrowDown, Info, SlidersHorizontal, History, Clock3, Undo2 } from 'lucide';
window.bootstrap = bootstrap;

const resetSubmittingButtons = (root = document) => {
    root.querySelectorAll('.app-submit-loading').forEach(button => {
        button.classList.remove('app-submit-loading');
        button.removeAttribute('aria-busy');
        button.disabled = false;
    });
};

const syncActiveSidebar = () => {
    const links = [...document.querySelectorAll('#appSidebar .sidebar-nav a[href]')];
    const currentPath = window.location.pathname.replace(/\/$/, '') || '/';
    let activeLink = null;
    let activeLength = -1;
    links.forEach(link => {
        const path = new URL(link.href, window.location.origin).pathname.replace(/\/$/, '') || '/';
        const matches = path === '/' ? currentPath === '/' : currentPath === path || currentPath.startsWith(path + '/');
        if (matches && path.length > activeLength) { activeLink = link; activeLength = path.length; }
    });
    links.forEach(link => link.classList.toggle('active', link === activeLink));
    return activeLink;
};

const syncSidebarLayout = () => {
    const sidebar = document.getElementById('appSidebar');
    const sidebarNav = sidebar?.querySelector('.sidebar-nav');
    const activeLink = syncActiveSidebar();

    sidebar?.classList.remove('show');
    document.getElementById('sidebarBackdrop')?.classList.remove('show');

    if (!sidebarNav || !activeLink) return;

    const dashboardLink = sidebarNav.querySelector('a[href]');
    if (activeLink === dashboardLink) {
        sidebarNav.scrollTop = 0;
        return;
    }

    requestAnimationFrame(() => {
        const linkTop = activeLink.offsetTop;
        const linkBottom = linkTop + activeLink.offsetHeight;
        const visibleTop = sidebarNav.scrollTop;
        const visibleBottom = visibleTop + sidebarNav.clientHeight;

        if (linkTop < visibleTop) sidebarNav.scrollTop = Math.max(0, linkTop - 14);
        else if (linkBottom > visibleBottom) sidebarNav.scrollTop = linkBottom - sidebarNav.clientHeight + 14;
    });
};

document.addEventListener('DOMContentLoaded', () => {
    syncSidebarLayout();
    const lucideIcons = { LayoutDashboard, Shapes, MapPin, Landmark, Package, QrCode, ScanQrCode, ScanLine, Camera, CameraOff, SwitchCamera, Focus, Keyboard, Download, ArrowLeftRight, ClipboardList, Wrench, Trash2, FileChartColumn, Users, Settings, LogOut, Menu, Bell, CircleCheck, CircleAlert, CircleX, TriangleAlert, Repeat2, CalendarDays, Plus, Eye, EyeOff, School, ShieldCheck, LockKeyhole, User, X, EllipsisVertical, Pencil, Power, Search, RotateCcw, Tags, Printer, ImagePlus, Save, ArrowLeft, ArrowRight, ArrowDown, Info, SlidersHorizontal, History, Clock3, Undo2 };
    window.refreshLucideIcons = () => createIcons({ icons: lucideIcons });
    window.refreshLucideIcons();

    const sidebar = document.getElementById('appSidebar');
    const sidebarNav = sidebar?.querySelector('.sidebar-nav');
    const backdrop = document.getElementById('sidebarBackdrop');
    const toggle = document.getElementById('sidebarToggle');
    const close = document.getElementById('sidebarClose');

    if (sidebarNav && !sidebarNav.dataset.navigationReady) {
        sidebarNav.dataset.navigationReady = '1';
        sidebarNav.querySelectorAll('a[href]').forEach(link => link.addEventListener('click', () => {
            if (window.innerWidth < 992) closeDrawer();
        }));
        sessionStorage.removeItem('inventorySidebarScroll');
    }

    requestAnimationFrame(() => requestAnimationFrame(() => document.documentElement.classList.remove('app-booting')));

    const closeDrawer = () => {
        sidebar?.classList.remove('show');
        backdrop?.classList.remove('show');
    };

    if (toggle && !toggle.dataset.sidebarReady) toggle.addEventListener('click', () => {
        if (window.innerWidth < 992) {
            sidebar?.classList.toggle('show');
            backdrop?.classList.toggle('show');
        } else {
            document.body.classList.toggle('sidebar-collapsed');
        }
    });
    if (toggle) toggle.dataset.sidebarReady = '1';
    if (backdrop && !backdrop.dataset.sidebarReady) { backdrop.addEventListener('click', closeDrawer); backdrop.dataset.sidebarReady = '1'; }
    if (close && !close.dataset.sidebarReady) { close.addEventListener('click', closeDrawer); close.dataset.sidebarReady = '1'; }

    if (!document.body.dataset.escapeReady) {
        document.addEventListener('keydown', event => { if (event.key === 'Escape') closeDrawer(); });
        document.body.dataset.escapeReady = '1';
    }

    document.querySelectorAll('form').forEach(form => {
        if (form.dataset.submitReady || form.hasAttribute('data-no-submit-loading') || (form.method || 'get').toLowerCase() === 'get') return;
        form.dataset.submitReady = '1';
        form.addEventListener('submit', event => {
            if (event.defaultPrevented || !form.checkValidity()) return;
            const button = event.submitter || form.querySelector('button[type="submit"], input[type="submit"]');
            if (!button || button.disabled) return;
            requestAnimationFrame(() => {
                button.disabled = true;
                button.classList.add('app-submit-loading');
                button.setAttribute('aria-busy', 'true');
            });
        });
    });

    document.querySelectorAll('img').forEach(image => {
        if (image.complete && image.naturalWidth === 0) image.classList.add('image-load-failed');
        if (!image.dataset.fallbackReady) {
            image.addEventListener('error', () => image.classList.add('image-load-failed'));
            image.dataset.fallbackReady = '1';
        }
    });

    document.querySelectorAll('.category-action').forEach(button => {
        if (!button.hasAttribute('aria-label')) button.setAttribute('aria-label', 'Buka menu tindakan');
        if (!button.hasAttribute('title')) button.setAttribute('title', 'Tindakan');
    });

    document.querySelectorAll('[data-auto-dismiss]').forEach(element => {
        window.setTimeout(() => bootstrap.Alert.getOrCreateInstance(element).close(), 4000);
    });
});

document.addEventListener('turbo:before-cache', () => {
    resetSubmittingButtons();
    document.querySelectorAll('.modal.show').forEach(modal => bootstrap.Modal.getInstance(modal)?.hide());
    document.body.classList.remove('modal-open');
    document.querySelectorAll('.modal-backdrop').forEach(backdrop => backdrop.remove());
});

document.addEventListener('turbo:before-visit', () => {
    document.body.classList.remove('sidebar-collapsed');
    document.getElementById('appSidebar')?.classList.remove('show');
    document.getElementById('sidebarBackdrop')?.classList.remove('show');
});

document.addEventListener('turbo:load', () => {
    resetSubmittingButtons();
    document.body.classList.remove('sidebar-collapsed');
    syncSidebarLayout();
    if (!document.documentElement.dataset.turboLoaded) {
        document.documentElement.dataset.turboLoaded = '1';
        return;
    }
    document.dispatchEvent(new Event('DOMContentLoaded'));
});

window.addEventListener('pageshow', () => {
    resetSubmittingButtons();
    document.body.classList.remove('sidebar-collapsed');
    syncSidebarLayout();
    document.dispatchEvent(new CustomEvent('app:submit-reset'));
});
