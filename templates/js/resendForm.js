let prompt, styleSelect, generateButton, loadingSpinner, sendButton, widthInput, alignmentButtons, finalPromptDisplay;
let originalButtonText = '';
let currentGeneratedImageUrl = null;

document.addEventListener("DOMContentLoaded", function () {
    const allTextareas = $("textarea");
    const allSelects = $("select");

    prompt = allTextareas.eq(0);
    styleSelect = allSelects.eq(0);

    // Capture width input
    widthInput = $("input[type='number']");
    if(widthInput.length === 0) {
        widthInput = $("input.form-control").eq(1);
    }

    generateButton = $("#redirectButton button");
    loadingSpinner = document.getElementById("loadingSpinner");
    sendButton = $('.il-standard-form-cmd button, .ilSubmitButton');
    finalPromptDisplay = $('#final-prompt-container input');

    setTimeout(() => {
        const targetLabel = $("label").filter(function() {
            return /Select style|Seleccionar estilo|Stil auswhlen/.test($(this).text());
        });

        if (targetLabel.length > 0) {
            $("#redirectButton").appendTo(targetLabel.closest(".form-group, .il-margin-bottom").find(".col-sm-8.col-md-9.col-lg-10, .il-prop-val"))
                .width("100%").children().css({ "margin-bottom": "10px", "width": "100%" });
        }
    }, 100);

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

    // --- LIVE PREVIEW ENGINE (SIZE & ALIGNMENT) ---

    function updateLivePreview() {
        const previewImg = $('#imageDiv img[alt="Generated_image"]');
        if(previewImg.length === 0) return;

        let sizeVal = widthInput.val();
        if(sizeVal && !isNaN(sizeVal)) {
            previewImg.css({
                'width': sizeVal + '%',
                'max-width': '100%',
                'height': 'auto',
                'transition': 'width 0.2s ease-out' // Smooth transition
            });
        }
    }

    // 1. Connect numeric input
    widthInput.on("input", function() {
        updateLivePreview();
        checkChanges();
    });

    // 2. Connect ILIAS range slider
    $(document).on("input change", "input[type='range']", function() {
        if(widthInput.length) widthInput.val($(this).val());
        updateLivePreview();
        checkChanges();
    });

    // 3. Connect alignment buttons
    $(document).on("click", "button", function() {
        const icon = $(this).find('span[class*="align"], img[src*="align"]');
        if (icon.length > 0) {
            let align = 'center';
            const iconClass = icon.attr('class') ? icon.attr('class').toLowerCase() : '';
            const iconSrc = icon.attr('src') ? icon.attr('src').toLowerCase() : '';

            if (iconClass.includes('left') || iconSrc.includes('left')) align = 'left';
            if (iconClass.includes('right') || iconSrc.includes('right')) align = 'right';

            $('#imageDiv').css({
                'text-align': align,
                'display': 'block',
                'width': '100%',
                'transition': 'text-align 0.3s ease'
            });
        }
    });

    // 4. Fallback for classic select element
    const alignSelect = $("select").filter(function() { return $(this).attr('name') && $(this).attr('name').toLowerCase().includes('align'); });
    if(alignSelect.length) {
        alignSelect.on("change", function() {
            $('#imageDiv').css({ 'text-align': $(this).val().toLowerCase(), 'display': 'block', 'width': '100%' });
        });
    }

    // Initialize preview
    updateLivePreview();
    checkChanges();
    updateFinalPromptDisplay();

    // Hide native ILIAS "Advanced Settings" link
    setInterval(function() {
        $("a").filter(function() {
            return /Advanced Settings|Ajustes avanzados|Erweiterte Einstellungen/.test($(this).text());
        }).hide();
    }, 500);
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
    if (resetButton.length) resetButton.click();

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
            if (typeof data === 'string') {
                try {
                    data = JSON.parse(data);
                } catch (e) {
                    console.warn("AIPic Parse Warning", e);
                }
            }

            if (data && data.Error && typeof data.Error === 'string' && data.Error.includes('b64_json')) {
                try {
                    const jsonStringMatch = data.Error.match(/\{[\s\S]*\}/);
                    if (jsonStringMatch) {
                        const rawJson = JSON.parse(jsonStringMatch[0]);
                        data.image = { mode: 'base64', value: rawJson.data[0].b64_json, mime: 'image/png' };
                        delete data.Error;
                    }
                } catch (e) {
                    console.error("Failed to recover Base64 payload:", e);
                }
            }

            let apiErrorMsg = null;
            if (data && data.error === true && data.message) {
                apiErrorMsg = data.message;
            } else if (data && data.Error) {
                if (typeof data.Error === 'string') {
                    try {
                        const parsedStr = JSON.parse(data.Error);
                        apiErrorMsg = (parsedStr && parsedStr.error === true && parsedStr.message) ? parsedStr.message : data.Error;
                    } catch (e) {
                        apiErrorMsg = data.Error;
                    }
                } else if (data.Error.message) {
                    apiErrorMsg = data.Error.message;
                } else {
                    apiErrorMsg = "Unknown AI integration error.";
                }
            }

            if (apiErrorMsg) {
                loadingSpinner.style.display = "none";
                setDisableFormControls(false);
                generateButton.text(originalButtonText);
                setDisableSendbuttons(false, false);

                displayMessage(`
                  <div class="alert alert-danger" role="alert">
                    <div class="ilAccHeadingHidden"><a name="il_message_focus">Error</a></div>
                    ${apiErrorMsg}
                  </div>
                `);
                return;
            }

            const downloadButton = document.getElementById("downloadButton");
            const imgDiv = document.getElementById("imageDiv");

            try {
                const imagePayload = data && data.image ? data.image : null;
                const blob = await getImageBlob(imagePayload, urlBase);
                const fileExtension = getFileExtension(blob.type || 'image/png');
                const file = new File([blob], `generated_image.${fileExtension}`, {type: blob.type || 'image/png'});
                const previewUrl = URL.createObjectURL(blob);

                if (currentGeneratedImageUrl) URL.revokeObjectURL(currentGeneratedImageUrl);
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
                    ['dragenter', 'dragover', 'drop'].forEach(eventName => {
                        dropzoneVisualElement.dispatchEvent(new DragEvent(eventName, { bubbles: true, cancelable: true, dataTransfer }));
                    });
                }

            } catch (error) {
                console.error("AIPic Debug Error:", error);
                loadingSpinner.style.display = "none";
                setDisableSendbuttons(false, false);
                setDisableFormControls(false);
                generateButton.text(originalButtonText);

                const redirectBtnEl = $("#redirectButton");
                let adminErrorText = redirectBtnEl.data("txt-error-admin") || "An error occurred. Please contact the administrator.";

                displayMessage(`
                  <div class="alert alert-danger" role="alert">
                    <div class="ilAccHeadingHidden"><a name="il_message_focus">Error</a></div>
                     ${adminErrorText}
                  </div>
                `);
                return;
            }

            if (imgDiv) {
                const targetImg = $(imgDiv).find('img[alt="Generated_image"]');
                if (targetImg.length) {
                    targetImg.attr('src', currentGeneratedImageUrl);
                    // Trigger forced resize
                    setTimeout(() => { widthInput.trigger('input'); }, 50);
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
    if (!imagePayload || !imagePayload.mode || !imagePayload.value) throw new Error('Invalid image payload structure');
    if (imagePayload.mode === 'base64') return base64ToBlob(imagePayload.value, imagePayload.mime || 'image/png');

    if (imagePayload.mode === 'url') {
        const currentUrl = new URL(urlBase, window.location.origin);
        currentUrl.search = window.location.search;
        currentUrl.searchParams.delete("urlDownload");
        currentUrl.searchParams.set("urlDownload", encodeURIComponent(imagePayload.value));
        currentUrl.searchParams.delete("methodDesired");
        currentUrl.searchParams.set("methodDesired", "downloadImage");

        const fetchUrl = currentUrl.pathname + "?" + currentUrl.searchParams.toString();
        const response = await fetch(fetchUrl);

        if (!response.ok) throw new Error(`Error downloading image from URL. HTTP Status: ${response.status}`);
        return response.blob();
    }

    throw new Error('Unsupported image payload mode: ' + imagePayload.mode);
}

function base64ToBlob(base64Value, mimeType) {
    let cleanBase64 = base64Value.replace(/\s/g, '');
    if (cleanBase64.includes(',')) cleanBase64 = cleanBase64.split(',')[1];

    const byteCharacters = atob(cleanBase64);
    const byteNumbers = new Array(byteCharacters.length);

    for (let i = 0; i < byteCharacters.length; i++) {
        byteNumbers[i] = byteCharacters.charCodeAt(i);
    }

    return new Blob([new Uint8Array(byteNumbers)], {type: mimeType});
}

function getFileExtension(mimeType) {
    const extensionMap = {
        'image/jpeg': 'jpg', 'image/jpg': 'jpg', 'image/png': 'png', 'image/gif': 'gif', 'image/webp': 'webp'
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
    return styleDesc.trim() ? `${userPrompt.trim()}, ${styleDesc}` : userPrompt.trim();
}

function isWidthInputEmpty() {
    const inputValue = widthInput.val()?.trim() || "";
    return !(inputValue !== "" && !isNaN(inputValue));
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
    $('input[type="range"]').prop('disabled', disabled);
    $('.aipic-btn-container button, .btn-group button').prop('disabled', disabled);
}

function checkChanges() {
    const imgDiv = document.getElementById("imageDiv");
    let imgEmptyOrDefault = true;

    if (imgDiv) {
        const targetImg = $(imgDiv).find('img[alt="Generated_image"]')[0];
        if (targetImg && targetImg.src !== "" && !targetImg.src.includes("placeholder")) imgEmptyOrDefault = false;
    }

    const isSpinnerVisible = loadingSpinner ? loadingSpinner.style.display === "block" : false;
    const promptEmpty = prompt ? prompt.val().length === 0 : true;

    setDisableSendbuttons(promptEmpty || isSpinnerVisible || isWidthInputEmpty(), imgEmptyOrDefault);
}

function displayMessage(htmlMessage) {
    let $messageArea = $("#global-message-area");
    if (!$messageArea.length) {
        $messageArea = $('<div id="global-message-area" style="margin-bottom: 15px;"></div>');
        $("#il_center_col").prepend($messageArea);
    }
    $messageArea.html(htmlMessage).show();

    const focusLink = $messageArea.find('a[name="il_message_focus"]');
    if (focusLink.length) focusLink.focus();
}

function clearMessage() {
    const $messageArea = $("#global-message-area");
    if ($messageArea.length) $messageArea.empty().hide();
}

function updateFinalPromptDisplay() {
    if(prompt && styleSelect && finalPromptDisplay) {
        finalPromptDisplay.val(setPromptStyle(prompt.val(), styleSelect.val()));
    }
}