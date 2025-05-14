function changePosition() {
    const value = $('select[name="AIPicForm/input_6/input_10"]').val();

    const imgDiv = $('#imageDiv');
    imgDiv.removeClass("left center right");
    imgDiv.addClass(value);
}

function changeSize() {
    const value = parseInt($('input[name="AIPicForm/input_6/input_11"]').val(), 10);

    const img = $('#imageDiv').children('img');
    img.removeClass('aipic-small aipic-medium aipic-large aipic-default-size');

    if (isNaN(value)) {
        img.addClass('aipic-default-size');
    } else {
        if (value <= 300) {
            img.addClass('aipic-small');
        } else if (value > 300 && value <= 500) {
            img.addClass('aipic-medium');
        } else if (value > 500) {
            img.addClass('aipic-large');
        } else {
            img.addClass('aipic-default-size');
        }
    }
}

document.addEventListener("DOMContentLoaded", function() {
    changePosition();
    changeSize();

    $('select[name="AIPicForm/input_6/input_10"]').on("change", changePosition);
    $('input[name="AIPicForm/input_6/input_11"]').on("input", changeSize);
});
