// Manage the disposition of the generate image button
$("#redirectButton")
    .appendTo($(".ui-input-textarea").parent())
    .width("100%")
    .children()
    .css("margin-bottom", "10px")
    .css("width", "100%");

const prompt = $(".il-section-input .ui-input-textarea textarea");
const styleSelect = $('select[name="AIPicForm/input_6/input_9"]');
const generateButton = $("#redirectButton button");
const loadingSpinner = document.getElementById("loadingSpinner");
const sendButton = $('button.btn.btn-default[data-action]');

document.addEventListener("DOMContentLoaded", function () {
    $(".ui-input-file-input-dropzone, .ui-input-file").hide();

    prompt.on("input", checkChanges);
    $('input[name="AIPicForm/input_6/input_11"]').on("input", () => {
        checkChanges();
        changeSize();
    });
    $('select[name="AIPicForm/input_6/input_10"]').on("input", changePosition);

    changePosition();
    changeSize();
    checkChanges();

});


function resendForm(url, urlBase) {

    const promptValue = setPromptStyle(prompt.val(), styleSelect.val());
    let fileInput = document.querySelector("input[type='file']");

    // Show the loading spinner
    loadingSpinner.style.display = "block";
    generateButton.attr("disabled", false);
    sendButton.attr("disabled", true);

    $.post(url, {
        prompt: promptValue,
    })
        .done(async function (data) {
            const downloadButton = document.getElementById("downloadButton");

            const imgDiv = document.getElementById("imageDiv");
            if (imgDiv.children[1].src) {
                try {
                    const currentUrl = new URL(urlBase, window.location.origin);
                    currentUrl.searchParams = new URLSearchParams(window.location.search);

                    currentUrl.searchParams.delete("urlDownload");
                    currentUrl.searchParams.set("urlDownload", encodeURI(data.image));
                    currentUrl.searchParams.delete("methodDesired");
                    currentUrl.searchParams.set("methodDesired", "downloadImage");

                    currentUrl.search = "?" + currentUrl.searchParams.toString();

                    // Convert the image URL to a blob and create a file
                    const response = await fetch(currentUrl);
                    const blob = await response.blob();
                    const file = new File([blob], "generated_image.png", {
                        type: blob.type,
                    });

                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);
                    fileInput.files = dataTransfer.files;
                    fileInput.dispatchEvent(new Event("change", {bubbles: true}));
                } catch (error) {
                    console.error("Error:", error);
                }
            }

            imgDiv.children[1].src = data.image;
            loadingSpinner.style.display = "none";
            downloadButton.style.display = "block";
            checkChanges();
        })
        .fail(function () {
            loadingSpinner.style.display = "none";
            generateButton.attr("disabled", false);
            sendButton.attr("disabled", false);
            alert("Error sending data");
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

function setDisableSendbuttons(status) {
    setTimeout(() => {
        generateButton.attr("disabled", status);
        sendButton.attr("disabled", status);
    }, 50);
}

function checkChanges() {
    const imgDiv = document.getElementById("imageDiv");
    const img = imgDiv.children[1];
    const imgEmptyOrDefault =
        img && (img.src === "" || img.src.includes("placeholder"));
    const promptEmpty =
        prompt.val().length === 0 || loadingSpinner.style.display === "block";
    const anyEmpty = promptEmpty || isWidthInputEmpty() || imgEmptyOrDefault;

    setDisableSendbuttons(anyEmpty);
}