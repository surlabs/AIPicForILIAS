function findSelectOriginal() {
    let selectOriginal = null;
    $('select').each(function() {
        let textoOpciones = $(this).text().toLowerCase();
        if (textoOpciones.includes('center') && textoOpciones.includes('left') && textoOpciones.includes('right')) {
            selectOriginal = $(this);
        }
    });
    return selectOriginal;
}

function applyAlignment(pos) {
    let imgs = document.querySelectorAll('img');
    let previewImg = null;

    for (let i = 0; i < imgs.length; i++) {
        if (imgs[i].clientWidth > 100 || (imgs[i].src && imgs[i].src.includes('placeholder'))) {
            previewImg = imgs[i];
            break;
        }
    }

    if (!previewImg) return;

    let groupDiv = previewImg.closest('.aipic-align-group');
    let innerWrapper = groupDiv ? groupDiv : previewImg.parentElement;
    if (innerWrapper) {
        if (!innerWrapper.parentElement.classList.contains('aipic-flex-wrapper')) {
            $(innerWrapper).wrap('<div class="aipic-flex-wrapper" style="display: flex; width: 100%;"></div>');
        }

        let flexWrapper = innerWrapper.parentElement;

        // Apply flex alignment
        if (pos === 'left') flexWrapper.style.setProperty('justify-content', 'flex-start');
        if (pos === 'center') flexWrapper.style.setProperty('justify-content', 'center');
        if (pos === 'right') flexWrapper.style.setProperty('justify-content', 'flex-end');

        innerWrapper.style.setProperty('margin', '0');
        if (!groupDiv) {
            innerWrapper.style.setProperty('display', 'block');
        }
    }
}

function updateSelectValue(value) {
    const select = findSelectOriginal();
    if (!select) return;

    // Apply visual alignment immediately
    applyAlignment(value);

    select.css({ 'opacity': '0', 'position': 'absolute', 'pointer-events': 'none', 'z-index': '-1' });
    
    // Find matching option (case insensitive)
    select.find('option').each(function() {
        if ($(this).text().toLowerCase().trim() === value) {
            select.val($(this).val()).trigger('change');
        }
    });
}

function createPositionButtons() {
    const svgIcons = {
        "left": `<svg fill="#FFFFFF" viewBox="0 0 24 24" width="25" height="25" xmlns="http://www.w3.org/2000/svg" transform="rotate(180)"><path d="M3,15H13a1,1,0,0,0,0-2H3a1,1,0,0,0,0,2Zm0-4H13a1,1,0,0,0,0-2H3a1,1,0,0,0,0,2ZM3,7H21a1,1,0,0,0,0-2H3A1,1,0,0,0,3,7ZM21,17H3a1,1,0,0,0,0,2H21a1,1,0,0,0,0-2ZM19.64,9.57a1,1,0,1,0-1.28,1.53l1.08.9-1.08.9a1,1,0,0,0-.13,1.41,1,1,0,0,0,.77.36,1,1,0,0,0,.64-.24l2-1.66a1,1,0,0,0,0-1.54Z"></path></svg>`,
        "center": `<svg fill="#FFFFFF" viewBox="0 0 24 24" width="25" height="25" xmlns="http://www.w3.org/2000/svg"><path d="M21.92,11.62a1,1,0,0,0-.21-.33l-2.5-2.5a1,1,0,0,0-1.42,1.42l.8.79H16a1,1,0,0,0,0,2h2.59l-.8.79a1,1,0,0,0,0,1.42,1,1,0,0,0,1.42,0l2.5-2.5a1,1,0,0,0,.21-.33A1,1,0,0,0,21.92,11.62ZM8,11H5.41l.8-.79A1,1,0,0,0,4.79,8.79l-2.5,2.5a1,1,0,0,0-.21.33,1,1,0,0,0,0,.76,1,1,0,0,0,.21.33l2.5,2.5a1,1,0,0,0,1.42,0,1,1,0,0,0,0-1.42L5.41,13H8a1,1,0,0,0,0-2Zm4-4a1,1,0,0,0-1,1v8a1,1,0,0,0,2,0V8A1,1,0,0,0,12,7Z"/></svg>`,
        "right": `<svg fill="#FFFFFF" viewBox="0 0 24 24" width="25" height="25" xmlns="http://www.w3.org/2000/svg"><path d="M3,15H13a1,1,0,0,0,0-2H3a1,1,0,0,0,0,2Zm0-4H13a1,1,0,0,0,0-2H3a1,1,0,0,0,0,2ZM3,7H21a1,1,0,0,0,0-2H3A1,1,0,0,0,3,7ZM21,17H3a1,1,0,0,0,0,2H21a1,1,0,0,0,0-2ZM19.64,9.57a1,1,0,1,0-1.28,1.53l1.08.9-1.08.9a1,1,0,0,0-.13,1.41,1,1,0,0,0,.77.36,1,1,0,0,0,.64-.24l2-1.66a1,1,0,0,0,0-1.54Z"></path></svg>`
    };

    const container = $('<div class="aipic-btn-container" style="display: flex;align-items: center; gap: 10px;"></div>');
    const positions = ['left', 'center', 'right'];

    positions.forEach(pos => {
        const btn = $(`<button type="button" class="btn btn-default" style="gap: 5px;"></button>`);
        btn.html(svgIcons[pos]);
        btn.on('click', function (e) {
            e.preventDefault();
            updateSelectValue(pos);
        });
        container.append(btn);
    });

    const select = findSelectOriginal();
    if (select) select.after(container);
}

document.addEventListener("DOMContentLoaded", function () {
    changePosition();
    const select = findSelectOriginal();
    if (select) {
        select.on("input change", changePosition);
        select.css({ 'opacity': '0', 'position': 'absolute', 'pointer-events': 'none', 'z-index': '-1' });
        createPositionButtons();

        setTimeout(() => {
            let currentText = select.find("option:selected").text().toLowerCase().trim();
            let initialPos = 'center';
            if(currentText.includes('left')) initialPos = 'left';
            if(currentText.includes('right')) initialPos = 'right';
            applyAlignment(initialPos);
        }, 500);
    }
});