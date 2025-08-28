// Manage the disposition of the generate image button
$("#redirectButton").appendTo(
    $("label").filter(function() {
        return /Select style|Seleccionar estilo|Stil auswählen/.test($(this).text());
    }).closest(".form-group").find(".col-sm-8.col-md-9.col-lg-10")
)
    .width("100%")
    .children()
    .css({
        "margin-bottom": "10px",
        "width": "100%"
    });

// Form elements selectors
const prompt = $(".il-section-input .ui-input-textarea textarea");
const styleSelect = $('select[name="AIPicForm/input_6/input_9"]');
const generateButton = $("#redirectButton button");
const loadingSpinner = document.getElementById("loadingSpinner");
const sendButton = $('.il-standard-form-cmd button');
const widthInput = $('input[name="AIPicForm/input_6/input_11"]');
const alignmentButtons = $('.aipic-btn-container button');
const finalPromptDisplay = $('#final-prompt-container input');
let originalButtonText = '';


document.addEventListener("DOMContentLoaded", function () {
    $(".ui-input-file-input-dropzone, .ui-input-file").hide();

    const redirectButtonDiv = $("#redirectButton");
    const txtGenerate = redirectButtonDiv.data("txt-generate");
    if (txtGenerate) {
        originalButtonText = txtGenerate;
        generateButton.text(originalButtonText);
    }else{
        originalButtonText = generateButton.text();
    }

    prompt.on("input", checkChanges);
    prompt.on("input", updateFinalPromptDisplay);
    styleSelect.on("change", updateFinalPromptDisplay);

    prompt.on("input", checkChanges);
    $('input[name="AIPicForm/input_6/input_11"]').on("input", () => {
        checkChanges();
        changeSize();
    });
    $('select[name="AIPicForm/input_6/input_10"]').on("input", changePosition);

    $('#imageDiv img[alt="Generated_image"]').css('width', '50%');
    changePosition();
    changeSize();
    checkChanges();
    updateFinalPromptDisplay();
});


function resendForm(url, urlBase) {

    const promptValue = setPromptStyle(prompt.val(), styleSelect.val());

    let dzInstance = null;
    let associatedFileInput = null;
    const dropzoneVisualElement = $('.ui-input-file .ui-input-file-input-dropzone').last()[0];
    clearMessage();
    if (dropzoneVisualElement) {
        const parentUiInputFile = dropzoneVisualElement.closest('.ui-input-file');

        if (Dropzone.instances && Dropzone.instances.length > 0) {
            dzInstance = Dropzone.instances.find(dz => dz.element === dropzoneVisualElement || (dz.hiddenFileInput && dz.hiddenFileInput.closest('.ui-input-file') === parentUiInputFile));
        }
    }
    if (!associatedFileInput && dropzoneVisualElement) {
        const parent = dropzoneVisualElement.closest('.ui-input-file');
        if (parent) associatedFileInput = parent.querySelector('input[type="file"]');
    }

    const resetButton = $('.glyphicon.glyphicon-remove')

    if (resetButton) {
        resetButton.click();
    }
    if (associatedFileInput) {
        associatedFileInput.value = "";
        associatedFileInput.dispatchEvent(new Event('change', {bubbles: true}));
    }

    loadingSpinner.style.display = "block";
    setDisableSendbuttons(true, true);
    setDisableFormControls(true);

    const redirectButtonDiv = $("#redirectButton");
    generateButton.text(redirectButtonDiv.data("txt-generating"));

    $.post(url, {prompt: promptValue})
        .done(async function (data) {
            const downloadButton = document.getElementById("downloadButton");
            const imgDiv = document.getElementById("imageDiv");

            try {
                const currentUrl = new URL(urlBase, window.location.origin);
                currentUrl.searchParams = new URLSearchParams(window.location.search);
                currentUrl.searchParams.delete("urlDownload");
                currentUrl.searchParams.set("urlDownload", encodeURI(data.image));
                currentUrl.searchParams.delete("methodDesired");
                currentUrl.searchParams.set("methodDesired", "downloadImage");

                const fetchUrl = currentUrl.pathname + "?" + currentUrl.searchParams.toString();
                const response = await fetch(fetchUrl);

                const blob = await response.blob();
                const file = new File([blob], "generated_image.png", {type: blob.type || 'image/png'});

                if (dzInstance) {
                    dzInstance.addFile(file);
                    if (dzInstance.files.includes(file) || dzInstance.files.some(f => f.name === file.name && f.size === file.size)) {
                    } else {
                        if (associatedFileInput) {
                            const dataTransfer = new DataTransfer();
                            dataTransfer.items.add(file);
                            associatedFileInput.files = dataTransfer.files;
                            associatedFileInput.dispatchEvent(new Event('change', {bubbles: true}));
                        }
                    }
                } else if (associatedFileInput) {
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);
                    associatedFileInput.files = dataTransfer.files;
                    associatedFileInput.dispatchEvent(new Event('change', {bubbles: true}));
                } else if (dropzoneVisualElement && typeof Dropzone !== 'undefined') {
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);
                    dropzoneVisualElement.dispatchEvent(new DragEvent('dragenter', {
                        bubbles: true,
                        cancelable: true,
                        dataTransfer
                    }));
                    dropzoneVisualElement.dispatchEvent(new DragEvent('dragover', {
                        bubbles: true,
                        cancelable: true,
                        dataTransfer
                    }));
                    dropzoneVisualElement.dispatchEvent(new DragEvent('drop', {
                        bubbles: true,
                        cancelable: true,
                        dataTransfer
                    }));
                }

            } catch (error) {
                loadingSpinner.style.display = "none";
                setDisableSendbuttons(false, false);
                setDisableFormControls(false);
                generateButton.text(originalButtonText);
                setDisableSendbuttons(false, false);
            }

            if (imgDiv && imgDiv.children && imgDiv.children.length > 1 && imgDiv.children[1].tagName === 'IMG') {
                imgDiv.children[1].src = data.image;
            }
            setTimeout(() => {
                loadingSpinner.style.display = "none";
                setDisableFormControls(false);
                generateButton.text(originalButtonText);
                checkChanges();
            }, 1500);

            if (downloadButton) downloadButton.style.display = "block";
            checkChanges();

        })
        .fail(function (jqXHR, textStatus, errorThrown) {
            setDisableSendbuttons(false, false);
            setTimeout(() => {
                loadingSpinner.style.display = "none";
                setDisableFormControls(false);
                generateButton.text(originalButtonText);
                checkChanges();
            }, 1500);

            displayMessage(`
  <div class="alert alert-danger" role="alert">
    <div class="ilAccHeadingHidden"><a name="il_message_focus">Error</a></div>
     An error occurred while generating the image. Please check the API configuration or try again later.
  </div>
`);
        });
}

function setPromptStyle(userPrompt, style) {
    const styleMap = {
        minimal:
            "minimal style, flat shapes, soft gradients, clean composition, limited color palette, focus on negative space, simple design",
        realistic:
            "realistic style, photorealistic rendering, high detail, natural lighting, accurate shadows, realistic textures, lifelike atmosphere",
        artistic:
            "artistic style, expressive strokes, creative lighting, stylized forms, surreal interpretation, painterly textures, vibrant mood",
        anime:
            "anime style, clean lines, cel shading, expressive faces, colorful background, exaggerated proportions, Japanese animation style",
        vintage:
            "vintage style, sepia tones, old film grain, retro color grading, nostalgic atmosphere, soft focus, 20th century aesthetic",
        cartoon:
            "cartoon style, bold outlines, flat colors, exaggerated features, playful expressions, simplified shapes, vibrant palette, animated look",
    };

    const styleDesc = styleMap[style] || "";

    if (!styleDesc.trim()) {
        return userPrompt.trim();
    }

    return `${userPrompt.trim()}, ${styleDesc}`;
}

function isWidthInputEmpty() {
    let res = true;
    const inputValue = $('input[name="AIPicForm/input_6/input_11"]').val().trim();

    if (inputValue !== "" && !isNaN(inputValue)) {
        res = false;
    }
    return res;
}

function setDisableSendbuttons(disableGen, disableSend) {
    setTimeout(() => {
        if (disableGen) {
            generateButton.prop("disabled", true);
        } else {
            generateButton.prop("disabled", false);
        }

        if (disableSend) {
            sendButton.attr("disabled", true);
        } else {
            sendButton.prop("disabled", false);
        }
    }, 50);
}

function setDisableFormControls(disabled) {
    prompt.prop('disabled', disabled);
    styleSelect.prop('disabled', disabled);
    widthInput.prop('disabled', disabled);
    $('#aipic_slider').prop('disabled', disabled);
    $('.aipic-btn-container button').prop('disabled', disabled);
}

function checkChanges() {
    const imgDiv = document.getElementById("imageDiv");
    const img = imgDiv.children[1];
    const imgEmptyOrDefault = !img || img.src === "" || img.src.includes("placeholder");
    const promptEmpty = prompt.val().length === 0 || loadingSpinner.style.display === "block";
    const anyEmpty = promptEmpty || isWidthInputEmpty();

    setDisableSendbuttons(anyEmpty, imgEmptyOrDefault);
}

function displayMessage(htmlMessage) {
    let $messageArea = $("#global-message-area");
    if (!$messageArea.length) {
        $messageArea = $('<div id="global-message-area" style="margin-bottom: 15px;"></div>');
        $("#il_center_col").prepend($messageArea);
    }
    $messageArea.html(htmlMessage).show();

    const focusLink = $messageArea.find('a[name="il_message_focus"]');
    if (focusLink.length) {
        focusLink.focus();
    }
}

function clearMessage() {
    const $messageArea = $("#global-message-area");
    if ($messageArea.length) {
        $messageArea.empty().hide();
    }
}

function updateFinalPromptDisplay() {
    const userPrompt = prompt.val();
    const style = styleSelect.val();
    const finalPrompt = setPromptStyle(userPrompt, style);
    finalPromptDisplay.val(finalPrompt);
}
