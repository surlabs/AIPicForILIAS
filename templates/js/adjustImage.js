function changePosition() {
    const select = $('select[name="AIPicForm/input_6/input_10"]');
    if (!select.length) return;

    let val = select.find("option:selected").text().toLowerCase().trim();
    if (!val) val = select.val().toLowerCase();

    // Find image
    let img = $('img[alt="AI Image"]');
    if (!img.length) {
        $('img').each(function() {
            if ($(this).width() > 100 || ($(this).attr('src') && $(this).attr('src').includes('placeholder'))) {
                img = $(this);
                return false;
            }
        });
    }
    if (!img.length) return;

    let groupDiv = img.closest('.aipic-align-group');
    let innerDiv = groupDiv.length ? groupDiv[0] : img.parent()[0];

    // 1. Wrap the image in a flexbox container
    if (!$(innerDiv).parent().hasClass('aipic-flex-wrapper')) {
        $(innerDiv).wrap('<div class="aipic-flex-wrapper" style="display: flex; width: 100%; margin: 0; padding: 0;"></div>');
    }

    let flexWrapper = $(innerDiv).parent()[0];

    // 2. Adjust parent elements to 100% width
    let parentEl = $(flexWrapper).parent();
    for (let i = 0; i < 5; i++) {
        if (parentEl.length && !parentEl.hasClass('form-group') && !parentEl.hasClass('row')) {
            parentEl[0].style.setProperty('width', '100%', 'important');
            parentEl[0].style.setProperty('display', 'block', 'important');
            parentEl[0].style.setProperty('max-width', 'none', 'important');

            // Clear padding/margin if in an ILIAS MediaContainer
            if (parentEl.hasClass('MediaContainer') || parentEl.hasClass('ilc_media_cont')) {
                parentEl[0].style.setProperty('padding', '0', 'important');
                parentEl[0].style.setProperty('margin', '0', 'important');
            }
            parentEl = parentEl.parent();
        } else {
            break;
        }
    }

    // 3. Move the image inside wrapper
    innerDiv.style.setProperty('margin', '0', 'important');

    if (val.includes('left') || val === 'left') {
        flexWrapper.style.setProperty('justify-content', 'flex-start', 'important');
    } else if (val.includes('right') || val === 'right') {
        flexWrapper.style.setProperty('justify-content', 'flex-end', 'important');
    } else {
        flexWrapper.style.setProperty('justify-content', 'center', 'important');
    }
}

function changeSize() {
    const inputSize = $('input[name="AIPicForm/input_6/input_11"]');
    if (!inputSize.length) return;

    const valueNumber = parseInt(inputSize.val(), 10);

    let img = $('img[alt="AI Image"]');
    if (!img.length) {
        $('img').each(function() {
            if ($(this).width() > 100 || ($(this).attr('src') && $(this).attr('src').includes('placeholder'))) {
                img = $(this);
                return false;
            }
        });
    }
    if (!img.length) return;

    let groupDiv = img.closest('.aipic-align-group');
    let innerDiv = groupDiv.length ? groupDiv[0] : img.parent()[0];

    if (innerDiv) {
        if (isNaN(valueNumber) || valueNumber <= 0) {
            innerDiv.style.setProperty('width', '1%', 'important');
        } else {
            innerDiv.style.setProperty('width', valueNumber + '%', 'important');
        }
    }
}

// Initialization
$(document).ready(function() {
    const selectAlign = $('select[name="AIPicForm/input_6/input_10"]');
    const inputSize = $('input[name="AIPicForm/input_6/input_11"]');

    selectAlign.on("change", function() {
        changePosition();
        setTimeout(changePosition, 100);
    });

    inputSize.on("change input", function() {
        changeSize();
    });

    setTimeout(changePosition, 500);
    setTimeout(changeSize, 500);
});