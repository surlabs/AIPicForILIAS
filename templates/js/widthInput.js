function createSlider(value) {
    const inputWidth = $('input[name="AIPicForm/input_6/input_11"]');

    const slider = $('<input>', {
        type: 'range',
        min: 1,
        value: value,
        max: 100,
        style: 'width: 100%; margin-top: 15px;'
    }).addClass('aipic_slider');
    inputWidth.after(slider);
    return slider.get(0);
}


function synchronizeInputs(input, slider) {
    slider.addEventListener("input", () => {
        input.value = slider.value;
        input.dispatchEvent(new Event("input", {bubbles: true}));
    });

    input.addEventListener("input", () => {
        let val = parseInt(input.value, 10);

        if ((val < -1) || (isNaN(val))) {
            val = 1;
        } else if (val > 100) {
            val = 100;
        } else if (val === 0) {
            val = 50;
        }
        input.value = val;
        slider.value = val;
        console.log("Slider value changed:", slider.value);
        console.log("input value changed:", input.value);

    });
}

const input = document.querySelector('input[name="AIPicForm/input_6/input_11"]');
const slider = createSlider(input.value);
synchronizeInputs(input, slider);
