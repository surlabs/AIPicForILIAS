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

    if (isNaN(value) || value <= 0) {
        img.css('width',  + '1%');
    } else {
        img.css('width', value + '%');
    }
}

document.addEventListener("DOMContentLoaded", function() {
    changePosition();
    changeSize();

    $('select[name="AIPicForm/input_6/input_10"]').on("input", changePosition);
    $('input[name="AIPicForm/input_6/input_11"]').on("input", changeSize);
});
