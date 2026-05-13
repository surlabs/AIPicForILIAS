let prompt, styleSelect, generateButton, loadingSpinner, sendButton, widthInput, alignmentButtons, finalPromptDisplay;
let originalButtonText = '';
let currentGeneratedImageUrl = null;

// Initialize on DOMContentLoaded to prevent race conditions with ILIAS core scripts
document.addEventListener("DOMContentLoaded", function () {
    prompt = $("fieldset .c-input__field textarea");
    styleSelect = $('select[name="AIPicForm/input_6/input_9"]');
    generateButton = $("#redirectButton button");
    loadingSpinner = document.getElementById("loadingSpinner");
    sendButton = $('.il-standard-form-cmd button');
    widthInput = $('input[name="AIPicForm/input_6/input_11"]');
    alignmentButtons = $('.aipic-btn-container button');
    finalPromptDisplay = $('#final-prompt-container input');

    $("#redirectButton").appendTo(
        $("label").filter(function() {
            return /Select style|Seleccionar estilo|Stil auswhlen/.test($(this).text());
        }).closest(".form-group").find(".col-sm-8.col-md-9.col-lg-10")
    )
        .width("100%")
        .children()
        .css({
            "margin-bottom": "10px",
            "width": "100%"
        });

    $(".ui-input-file-input-dropzone, .ui-input-file").hide();

    const redirectButtonDiv = $("#redirectButton");
    const txtGenerate = redirectButtonDiv.data("txt-generate");
    if (txtGenerate) {
        originalButtonText = txtGenerate;
        generateButton.text(originalButtonText);
    } else {
        originalButtonText = generateButton.text();
    }

    prompt.on("input", checkChanges);
    prompt.on("input", updateFinalPromptDisplay);
    styleSelect.on("change", updateFinalPromptDisplay);

    $('input[name="AIPicForm/input_6/input_11"]').on("input", () => {
        checkChanges();
        if (typeof changeSize === "function") changeSize();
    });

    if (typeof changePosition === "function") {
        $('select[name="AIPicForm/input_6/input_10"]').on("input", changePosition);
    }

    $('#imageDiv img[alt="Generated_image"]').css('width', '50%');
    if (typeof changePosition === "function") changePosition();
    if (typeof changeSize === "function") changeSize();

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

    const resetButton = $('.glyphicon.glyphicon-remove');
    if (resetButton.length) {
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

            // --- START: BASE64 INTERCEPTOR ---
            // Recover the image if the backend failed and returned the JSON within an error string
            if (data && data.Error && typeof data.Error === 'string' && data.Error.includes('b64_json')) {
                try {
                    // 1. Extract the valid JSON portion from the error string using a Regular Expression
                    const jsonStringMatch = data.Error.match(/\{[\s\S]*\}/);

                    if (jsonStringMatch) {
                        // 2. Parse the raw text into a manageable JavaScript object
                        const rawJson = JSON.parse(jsonStringMatch[0]);

                        // 3. Navigate the OpenAI response structure to extract the Base64 image string
                        const b64String = rawJson.data[0].b64_json;

                        // 4. Construct the payload exactly as the getImageBlob() function expects it
                        data.image = {
                            mode: 'base64',
                            value: b64String,
                            mime: 'image/png'
                        };

                        // 5. Remove the error flag to allow the execution flow to proceed normally
                        delete data.Error;
                    }
                } catch (e) {
                    console.error("Failed to recover Base64 payload:", e);
                }
            }
            // --- END: BASE64 INTERCEPTOR ---

            // Catch AI moderation filter rejections
            if (data && data.Error) {
                console.log("Raw AI response:", data.Motivo_Real_IA);
                let errorMsg = data.Motivo_Real_IA ? ` (${data.Motivo_Real_IA})` : "";
                throw new Error("AI Model rejected the prompt or returned no image. " + data.Error + errorMsg);
            }
            const downloadButton = document.getElementById("downloadButton");
            const imgDiv = document.getElementById("imageDiv");

            try {
                const imagePayload = data && data.image ? data.image : null;
                const blob = await getImageBlob(imagePayload, urlBase);
                const fileExtension = getFileExtension(blob.type || 'image/png');
                const file = new File([blob], `generated_image.${fileExtension}`, {type: blob.type || 'image/png'});
                const previewUrl = URL.createObjectURL(blob);

                if (currentGeneratedImageUrl) {
                    URL.revokeObjectURL(currentGeneratedImageUrl);
                }
                currentGeneratedImageUrl = previewUrl;

                if (dzInstance) {
                    dzInstance.addFile(file);
                } else if (associatedFileInput) {
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);
                    associatedFileInput.files = dataTransfer.files;
                    associatedFileInput.dispatchEvent(new Event('change', {bubbles: true}));
                } else if (dropzoneVisualElement && typeof Dropzone !== 'undefined') {
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);
                    const events = ['dragenter', 'dragover', 'drop'];
                    events.forEach(eventName => {
                        dropzoneVisualElement.dispatchEvent(new DragEvent(eventName, {
                            bubbles: true, cancelable: true, dataTransfer
                        }));
                    });
                }

            } catch (error) {
                console.error("AIPic Debug Error:", error);
                loadingSpinner.style.display = "none";
                setDisableSendbuttons(false, false);
                setDisableFormControls(false);
                generateButton.text(originalButtonText);
                displayMessage(`
                  <div class="alert alert-danger" role="alert">
                    <div class="ilAccHeadingHidden"><a name="il_message_focus">Error</a></div>
                     An error occurred while processing the generated image. Please try again later. Check the console for details.
                  </div>
                `);
                return;
            }

            // Safe DOM search
            if (imgDiv) {
                const targetImg = $(imgDiv).find('img[alt="Generated_image"]');
                if (targetImg.length) {
                    targetImg.attr('src', currentGeneratedImageUrl);
                }
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
            console.error("AIPic Network Fail:", textStatus, errorThrown);
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
                 An error occurred while communicating with the server. Please check your connection.
              </div>
            `);
        });
}

async function getImageBlob(imagePayload, urlBase) {
    if (!imagePayload || !imagePayload.mode || !imagePayload.value) {
        throw new Error('Invalid image payload structure');
    }

    if (imagePayload.mode === 'base64') {
        return base64ToBlob(imagePayload.value, imagePayload.mime || 'image/png');
    }

    if (imagePayload.mode === 'url') {
        const currentUrl = new URL(urlBase, window.location.origin);
        currentUrl.search = window.location.search;
        currentUrl.searchParams.delete("urlDownload");

        // Preserve AI signature tokens using encodeURIComponent
        currentUrl.searchParams.set("urlDownload", encodeURIComponent(imagePayload.value));

        currentUrl.searchParams.delete("methodDesired");
        currentUrl.searchParams.set("methodDesired", "downloadImage");

        const fetchUrl = currentUrl.pathname + "?" + currentUrl.searchParams.toString();
        const response = await fetch(fetchUrl);

        if (!response.ok) {
            throw new Error(`Error downloading image from URL. HTTP Status: ${response.status}`);
        }

        return response.blob();
    }

    throw new Error('Unsupported image payload mode: ' + imagePayload.mode);
}

function base64ToBlob(base64Value, mimeType) {
    let cleanBase64 = base64Value.replace(/\s/g, '');

    // Strip metadata headers to prevent DOMExceptions
    if (cleanBase64.includes(',')) {
        cleanBase64 = cleanBase64.split(',')[1];
    }

    const byteCharacters = atob(cleanBase64);
    const byteNumbers = new Array(byteCharacters.length);

    for (let i = 0; i < byteCharacters.length; i++) {
        byteNumbers[i] = byteCharacters.charCodeAt(i);
    }

    return new Blob([new Uint8Array(byteNumbers)], {type: mimeType});
}

function getFileExtension(mimeType) {
    const extensionMap = {
        'image/jpeg': 'jpg',
        'image/jpg': 'jpg',
        'image/png': 'png',
        'image/gif': 'gif',
        'image/webp': 'webp'
    };
    return extensionMap[mimeType] || 'png';
}

function setPromptStyle(userPrompt, style) {
    const styleMap = {
        minimal: "minimal style, flat shapes, soft gradients, clean composition, limited color palette, focus on negative space, simple design",
        realistic: "realistic style, photorealistic rendering, high detail, natural lighting, accurate shadows, realistic textures, lifelike atmosphere",
        artistic: "artistic style, expressive strokes, creative lighting, stylized forms, surreal interpretation, painterly textures, vibrant mood",
        anime: "anime style, clean lines, cel shading, expressive faces, colorful background, exaggerated proportions, Japanese animation style",
        vintage: "vintage style, sepia tones, old film grain, retro color grading, nostalgic atmosphere, soft focus, 20th century aesthetic",
        cartoon: "cartoon style, bold outlines, flat colors, exaggerated features, playful expressions, simplified shapes, vibrant palette, animated look",
    };

    const styleDesc = styleMap[style] || "";
    if (!styleDesc.trim()) {
        return userPrompt.trim();
    }
    return `${userPrompt.trim()}, ${styleDesc}`;
}

function isWidthInputEmpty() {
    let res = true;
    const inputValue = $('input[name="AIPicForm/input_6/input_11"]').val()?.trim() || "";
    if (inputValue !== "" && !isNaN(inputValue)) {
        res = false;
    }
    return res;
}

function setDisableSendbuttons(disableGen, disableSend) {
    setTimeout(() => {
        if (generateButton && generateButton.length) generateButton.prop("disabled", disableGen);
        if (sendButton && sendButton.length) sendButton.prop("disabled", disableSend);
    }, 50);
}

function setDisableFormControls(disabled) {
    if(prompt) prompt.prop('disabled', disabled);
    if(styleSelect) styleSelect.prop('disabled', disabled);
    if(widthInput) widthInput.prop('disabled', disabled);
    $('#aipic_slider').prop('disabled', disabled);
    $('.aipic-btn-container button').prop('disabled', disabled);
}

function checkChanges() {
    const imgDiv = document.getElementById("imageDiv");
    let imgEmptyOrDefault = true;

    if (imgDiv) {
        const targetImg = $(imgDiv).find('img[alt="Generated_image"]')[0];
        if (targetImg && targetImg.src !== "" && !targetImg.src.includes("placeholder")) {
            imgEmptyOrDefault = false;
        }
    }

    const isSpinnerVisible = loadingSpinner ? loadingSpinner.style.display === "block" : false;
    const promptEmpty = prompt ? prompt.val().length === 0 : true;
    const anyEmpty = promptEmpty || isSpinnerVisible || isWidthInputEmpty();

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
    if(prompt && styleSelect && finalPromptDisplay) {
        const userPrompt = prompt.val();
        const style = styleSelect.val();
        const finalPrompt = setPromptStyle(userPrompt, style);
        finalPromptDisplay.val(finalPrompt);
    }
}
