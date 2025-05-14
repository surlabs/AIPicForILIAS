// Configure the position of the image
$('select[name="AIPicForm/input_6/input_10"]').on("change", function () {
    const value = $(this).val();
    console.log("Selected value:", value);

    // We have to apply the value to the style of the image
    const imgDiv = $('#imageDiv');
    imgDiv.removeClass("left center right");
    imgDiv.addClass(value);
})

// Configure the size of the image
$('input[name="AIPicForm/input_6/input_11"]').on("change", function () {
    const value = parseInt($(this).val(), 10);

    const img = $('#imageDiv').children('img');
    console.log(img);

    img.removeClass('aipic-small aipic-medium aipic-large aipic-default-size');

    if (isNaN(value)) {
        img.addClass('aipic-default-size');
        console.log("Invalid value, defaulting size.");
    } else {
        if (value <= 300) {
            img.addClass('aipic-small');
            console.log("Selected value: small");
        } else if (value > 300 && value <= 500) {
            img.addClass('aipic-medium');
            console.log("Selected value: medium");
        } else if (value > 500) {
            img.addClass('aipic-large');
            console.log("Selected value: large");
        } else {
            img.addClass('aipic-default-size');
            console.log("Selected value: default");
        }
    }
});
