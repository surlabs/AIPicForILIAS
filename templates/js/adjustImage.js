// Configure the position of the image
$('select[name="AIPicForm/input_6/input_10"]').on("change", function changePosition() {
    const value = $(this).val();

    // We have to apply the value to the style of the image
    const imgDiv = $('#imageDiv');
    imgDiv.removeClass("left center right");
    imgDiv.addClass(value);
})

// Configure the size of the image
$('input[name="AIPicForm/input_6/input_11"]').on("change", function changeSize() {
    const value = parseInt($(this).val(), 10);

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
});

