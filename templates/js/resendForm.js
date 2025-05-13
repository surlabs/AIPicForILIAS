$("#redirectButton").appendTo($(".ui-input-textarea").parent()).width("100%").children().css("margin-bottom", "10px").css("width", "100%");
$("#imageDiv").css("max-width", "800px").css("max", "100%");

$("downloadButton").children().css("width", "10%");

for (let i = 0; i < document.getElementsByClassName("ui-input-file-input-dropzone").length; i++) {
    document.getElementsByClassName("ui-input-file-input-dropzone")[i].style.display = "none";
}

const prompt = $('.il-section-input .ui-input-textarea textarea');
const styleSelect = $('select[name="AIPicForm/input_6/input_9"]');


const generateButton = $('#redirectButton button');
const widthInputs = document.getElementsByName("AIPicForm/input_5/input_9")
const loadingSpinner = document.getElementById('loadingSpinner');

function isWidthInputEmpty() {
    let res = true;
    for (let i = 0; i < widthInputs.length; i++) {
        if (widthInputs[i].value && widthInputs[i].value.trim() !== "") {
            res = false;
        }
    }
    return res;
}

function setDisableSendbuttons(status) {
    setTimeout(()=>{
        $(".il-standard-form-cmd button").attr("disabled", status);
    }, 50);
}

function checkChanges() {
    const imgDiv = document.getElementById('imageDiv');
    const imgEmptyOrDefault = imgDiv.children[1].src === "" || imgDiv.children[1].src.toLowerCase().includes("placeholder");
    const promtEmpty = prompt.val().length === 0 || loadingSpinner.style.display === 'block';
    const anyEmpty = promtEmpty || isWidthInputEmpty() || imgEmptyOrDefault;
    setDisableSendbuttons(anyEmpty);
    generateButton.attr("disabled", promtEmpty);
}

document.addEventListener("DOMContentLoaded", function() {
    prompt.on('input', checkChanges);
    checkChanges();
    widthInputs.forEach(input => {
        input.addEventListener("input", checkChanges);
    });
    for (let i = 0; i < document.getElementsByClassName("ui-input-file-input-dropzone").length; i++) {
        document.getElementsByClassName("ui-input-file-input-dropzone")[i].style.display = "none";
    }
    for (let i = 0; i < document.getElementsByClassName("ui-input-file").length; i++) {
        document.getElementsByClassName("ui-input-file")[i].style.display = "none";
    }

    setDisableSendbuttons(true);
});

function getFormData(form) {
    const formData = {};
    const elements = form.elements;

    for (let i = 0; i < elements.length; i++) {
        const element = elements[i];
        const name = element.name;
        const value = element.value;

        if (name) {
            formData["prompt"] = value;
        }
    }

    return formData;
}

function updateDownloadUrl(newUrl) {
    $("#downloadButton").attr("data-url", newUrl);
}


function resendForm(url, urlBase) {

    const promptValue = setPromptStyle(prompt.val(), styleSelect.val());

    let fileInput = document.querySelector("input[type='file']");

    // Show the loading spinner
    loadingSpinner.style.display = 'block';

    generateButton.attr("disabled", true);

    $.post(url, {
        prompt: promptValue
    })
        .done(async function (data) {
            const downloadButton = document.getElementById('downloadButton');

            const imgDiv = document.getElementById('imageDiv');
            if (imgDiv.children[1].src) {
                try {
                    const currentUrl = new URL(urlBase, window.location.origin);
                    currentUrl.searchParams = new URLSearchParams(window.location.search);

                    currentUrl.searchParams.delete("urlDownload");
                    currentUrl.searchParams.set("urlDownload", encodeURI(data.image));
                    currentUrl.searchParams.delete("methodDesired");
                    currentUrl.searchParams.set("methodDesired", "downloadImage");

                    currentUrl.search = "?" + currentUrl.searchParams.toString();

                    // Convertir la imagen generada a un Blob y asignarla al input file
                    const response = await fetch(currentUrl);
                    const blob = await response.blob();
                    const file = new File([blob], "generated_image.png", { type: blob.type });

                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);
                    fileInput.files = dataTransfer.files;
                    fileInput.dispatchEvent(new Event('change', { bubbles: true }));

                } catch (error) {
                    console.error("Error:", error);
                }
            }

            imgDiv.children[1].src = data.image;
            loadingSpinner.style.display = 'none';
            downloadButton.style.display = "block";
            checkChanges();
        })
        .fail(function () {
            loadingSpinner.style.display = 'none';
            generateButton.attr("disabled", false);
            alert("Error sending data");
        });
}

function setPromptStyle(userPrompt, style) {
    const styleMap = {
        minimal: 'minimal style, flat shapes, soft gradients, clean composition, limited color palette, focus on negative space, simple design',
        realistic: 'realistic style, photorealistic rendering, high detail, natural lighting, accurate shadows, realistic textures, lifelike atmosphere',
        artistic: 'artistic style, expressive strokes, creative lighting, stylized forms, surreal interpretation, painterly textures, vibrant mood'
    };

    const styleDesc = styleMap[style] || '';

    if (!styleDesc.trim()) {
        return userPrompt.trim();
    }

    return `${userPrompt.trim()}, ${styleDesc}`;
}
