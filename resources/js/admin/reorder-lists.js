function updatePositions(list) {
    const items = list.querySelectorAll('[data-reorder-item]:not(.catalog-reorder__item--origin-hidden)');

    items.forEach((item, index) => {
        const position = item.querySelector('[data-reorder-position]');

        if (position) {
            position.textContent = String(index + 1);
        }
    });
}

function getScrollContainer(element) {
    let current = element.parentElement;

    while (current && current !== document.body) {
        const styles = window.getComputedStyle(current);
        const canScroll = /(auto|scroll)/.test(styles.overflowY) && current.scrollHeight > current.clientHeight;

        if (canScroll) {
            return current;
        }

        current = current.parentElement;
    }

    return document.scrollingElement || document.documentElement;
}

function scrollContainerBy(container, delta) {
    if (container === document.documentElement || container === document.body || container === document.scrollingElement) {
        window.scrollBy(0, delta);
        return;
    }

    container.scrollTop += delta;
}

function maybeAutoScroll(container, pointerY) {
    const rect = container === document.documentElement || container === document.body || container === document.scrollingElement
        ? { top: 0, bottom: window.innerHeight }
        : container.getBoundingClientRect();

    const threshold = 72;
    const speed = 18;

    if (pointerY < rect.top + threshold) {
        scrollContainerBy(container, -speed);
    } else if (pointerY > rect.bottom - threshold) {
        scrollContainerBy(container, speed);
    }
}

function movePlaceholder(list, placeholder, pointerX, pointerY) {
    const hoveredElement = document.elementFromPoint(pointerX, pointerY);
    const hoveredItem = hoveredElement?.closest('[data-reorder-item]');

    if (hoveredItem && hoveredItem.parentElement === list && hoveredItem !== placeholder) {
        const hoveredRect = hoveredItem.getBoundingClientRect();
        const shouldInsertAfter = pointerY > hoveredRect.top + (hoveredRect.height / 2);

        if (shouldInsertAfter) {
            list.insertBefore(placeholder, hoveredItem.nextSibling);
        } else {
            list.insertBefore(placeholder, hoveredItem);
        }

        return;
    }

    const listRect = list.getBoundingClientRect();

    if (pointerY <= listRect.top + 24) {
        list.insertBefore(placeholder, list.firstElementChild);
        return;
    }

    if (pointerY >= listRect.bottom - 24) {
        list.appendChild(placeholder);
    }
}

function createDragPreview(item, rect) {
    const preview = item.cloneNode(true);

    preview.querySelectorAll('input[type="hidden"]').forEach((input) => input.remove());
    preview.classList.add('catalog-reorder__item--dragging');
    preview.style.width = `${rect.width}px`;
    preview.style.height = `${rect.height}px`;
    preview.style.left = `${rect.left}px`;
    preview.style.top = `${rect.top}px`;

    document.body.appendChild(preview);

    return preview;
}

let activeDragState = null;

function handlePointerMove(event) {
    if (!activeDragState || event.pointerId !== activeDragState.pointerId) {
        return;
    }

    event.preventDefault();

    activeDragState.preview.style.top = `${event.clientY - activeDragState.offsetY}px`;
    maybeAutoScroll(activeDragState.scrollContainer, event.clientY);
    movePlaceholder(activeDragState.list, activeDragState.placeholder, event.clientX, event.clientY);
}

function finishActiveDrag(event) {
    if (!activeDragState || event.pointerId !== activeDragState.pointerId) {
        return;
    }

    activeDragState.list.insertBefore(activeDragState.item, activeDragState.placeholder);
    activeDragState.item.classList.remove('catalog-reorder__item--origin-hidden');
    activeDragState.placeholder.remove();
    activeDragState.preview.remove();
    document.body.classList.remove('reorder-dragging-active');

    updatePositions(activeDragState.list);
    activeDragState = null;
}

function initReorderList(container) {
    if (container.dataset.reorderReady === 'true') {
        return;
    }

    const list = container.querySelector('[data-reorder-items]');

    if (!list) {
        return;
    }

    container.dataset.reorderReady = 'true';
    updatePositions(list);

    container.addEventListener('pointerdown', (event) => {
        if (activeDragState) {
            return;
        }

        const item = event.target.closest('[data-reorder-item]');
        const handle = event.target.closest('[data-reorder-handle]');

        if (!item || item.parentElement !== list) {
            return;
        }

        if (event.pointerType === 'touch' && !handle) {
            return;
        }

        if (event.pointerType === 'mouse' && event.button !== 0) {
            return;
        }

        if (!handle && event.target.closest('button, a, input, textarea, select, label')) {
            return;
        }

        const rect = item.getBoundingClientRect();
        const placeholder = document.createElement('li');

        placeholder.className = 'list-group-item catalog-reorder__placeholder';
        placeholder.style.height = `${rect.height}px`;
        placeholder.setAttribute('aria-hidden', 'true');

        item.insertAdjacentElement('afterend', placeholder);
        item.classList.add('catalog-reorder__item--origin-hidden');

        activeDragState = {
            list,
            item,
            placeholder,
            preview: createDragPreview(item, rect),
            pointerId: event.pointerId,
            offsetY: event.clientY - rect.top,
            scrollContainer: getScrollContainer(list),
        };

        document.body.classList.add('reorder-dragging-active');
        event.preventDefault();
    });
}

function initReorderLists(root = document) {
    root.querySelectorAll('[data-reorder-list]').forEach((container) => {
        initReorderList(container);
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initReorderLists();
});

window.addEventListener('pointermove', handlePointerMove);
window.addEventListener('pointerup', finishActiveDrag);
window.addEventListener('pointercancel', finishActiveDrag);

export { initReorderLists };
