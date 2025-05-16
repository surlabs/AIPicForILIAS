function updateSelectValue(value) {
    const select = $('select[name="AIPicForm/input_6/input_10"]');
    select.hide();
    select.val(value).trigger('change');
}

function createPositionButtons() {
    const svgIcons = {
        "left": `
           <svg fill="#FFFFFF" viewBox="0 0 24 24" width="25" height="25" xmlns="http://www.w3.org/2000/svg" transform="rotate(180)"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><path d="M3,15H13a1,1,0,0,0,0-2H3a1,1,0,0,0,0,2Zm0-4H13a1,1,0,0,0,0-2H3a1,1,0,0,0,0,2ZM3,7H21a1,1,0,0,0,0-2H3A1,1,0,0,0,3,7ZM21,17H3a1,1,0,0,0,0,2H21a1,1,0,0,0,0-2ZM19.64,9.57a1,1,0,1,0-1.28,1.53l1.08.9-1.08.9a1,1,0,0,0-.13,1.41,1,1,0,0,0,.77.36,1,1,0,0,0,.64-.24l2-1.66a1,1,0,0,0,0-1.54Z"></path></g></svg>`,
        "center": `
            <svg fill="#FFFFFF" viewBox="0 0 24 24" width="25" height="25" xmlns="http://www.w3.org/2000/svg">
                <path d="M21.92,11.62a1,1,0,0,0-.21-.33l-2.5-2.5a1,1,0,0,0-1.42,1.42l.8.79H16a1,1,0,0,0,0,2h2.59l-.8.79a1,1,0,0,0,0,1.42,1,1,0,0,0,1.42,0l2.5-2.5a1,1,0,0,0,.21-.33A1,1,0,0,0,21.92,11.62ZM8,11H5.41l.8-.79A1,1,0,0,0,4.79,8.79l-2.5,2.5a1,1,0,0,0-.21.33,1,1,0,0,0,0,.76,1,1,0,0,0,.21.33l2.5,2.5a1,1,0,0,0,1.42,0,1,1,0,0,0,0-1.42L5.41,13H8a1,1,0,0,0,0-2Zm4-4a1,1,0,0,0-1,1v8a1,1,0,0,0,2,0V8A1,1,0,0,0,12,7Z"/>
            </svg>`,
        "right": `
            <svg fill="#FFFFFF" viewBox="0 0 24 24" width="25" height="25" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><path d="M3,15H13a1,1,0,0,0,0-2H3a1,1,0,0,0,0,2Zm0-4H13a1,1,0,0,0,0-2H3a1,1,0,0,0,0,2ZM3,7H21a1,1,0,0,0,0-2H3A1,1,0,0,0,3,7ZM21,17H3a1,1,0,0,0,0,2H21a1,1,0,0,0,0-2ZM19.64,9.57a1,1,0,1,0-1.28,1.53l1.08.9-1.08.9a1,1,0,0,0-.13,1.41,1,1,0,0,0,.77.36,1,1,0,0,0,.64-.24l2-1.66a1,1,0,0,0,0-1.54Z"></path></g></svg>`
    };

    const container = $('<div class="aipic-btn-container" style="display: flex;align-items: center; gap: 10px;"></div>');
    const positions = ['left', 'center', 'right'];

    positions.forEach(pos => {
        const btn = $(`<button type="button" class="btn btn-default" style="gap: 5px;"></button>`);
        btn.html(svgIcons[pos]);
        btn.on('click', function () {
            updateSelectValue(pos);
        });
        container.append(btn);
    });

    const select = $('select[name="AIPicForm/input_6/input_10"]');
    select.after(container);
}

document.addEventListener("DOMContentLoaded", function () {
    changePosition();
    const select = $('select[name="AIPicForm/input_6/input_10"]');
    select.on("input change", changePosition);
    select.hide();
    createPositionButtons();
});
