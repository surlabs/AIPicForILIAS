$(document).ready(function () {
    function getMainColor() {
        const btn = $("body .btn-default")[0];
        return btn
            ? window.getComputedStyle(btn).backgroundColor
            : 'rgb(0,0,0)';
    }

    // Dynamic and safe search for the width field
    function getWidthInput() {
        let input = $("input[type='number']");
        if (input.length === 0) {
            input = $("input.form-control").eq(1); // Fallback in case ILIAS renders it as text
        }
        return input;
    }

    function createSlider(value) {
        const inputWidth = getWidthInput();
        const slider = $('<input>', {
            type: 'range',
            min: 1,
            max: 100,
            value: value || 50,
            id: 'aipic_slider',
            style: 'width: 100%; margin-top: 15px;'
        }).addClass('aipic_slider');
        inputWidth.after(slider);
        return slider.get(0);
    }

    function synchronizeInputs(input, slider) {
        slider.addEventListener('input', () => {
            input.value = slider.value;
            input.dispatchEvent(new Event('input', {bubbles: true}));
        });

        input.addEventListener('input', () => {
            let val = parseInt(input.value, 10);
            if (val < 1 || isNaN(val)) val = 1;
            else if (val > 100) val = 100;
            else if (val === 0) val = 50;
            input.value = val;
            slider.value = val;
        });
    }

    function applySliderStyles(slider, mainColor) {
        const rgbMatch = mainColor.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
        const rgb = rgbMatch
            ? `${rgbMatch[1]}, ${rgbMatch[2]}, ${rgbMatch[3]}`
            : `0, 0, 0`;

        slider.style.setProperty('--main-color-rgb', rgb);

        const css = `
            #${slider.id} {
                -webkit-appearance: none;
                background: transparent;
                margin-bottom: 15px;
            }
            /* Track */
            #${slider.id}::-webkit-slider-runnable-track {
                height: 8px;
                background: rgba(var(--main-color-rgb), 0.4);
                border-radius: 4px;
            }
            #${slider.id}::-moz-range-track {
                height: 8px;
                background: rgba(var(--main-color-rgb), 0.4);
                border-radius: 4px;
            }
            /* Thumb */
            #${slider.id}::-webkit-slider-thumb {
                -webkit-appearance: none;
                width: 24px;
                height: 24px;
                background: rgb(var(--main-color-rgb));
                border-radius: 50%;
                border: none;
                margin-top: -8px;
                cursor: pointer;
            }
            #${slider.id}::-moz-range-thumb {
                width: 24px;
                height: 24px;
                background: rgb(var(--main-color-rgb));
                border-radius: 50%;
                border: none;
                cursor: pointer;
            }
            #${slider.id}:focus {
                outline: none;
            }
        `;
        $('<style>').text(css).appendTo('head');
    }

    // Safe initialization
    const inputObj = getWidthInput();
    if (inputObj.length > 0) {
        const input = inputObj.get(0);
        const mainColor = getMainColor();
        const slider = createSlider(input.value);
        synchronizeInputs(input, slider);
        applySliderStyles(slider, mainColor);
    }
});