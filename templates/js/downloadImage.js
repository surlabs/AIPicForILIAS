function downloadImage(imageUrl, fileName = "imagen") {
    fetch(imageUrl, {mode: "no-cors"})
        .then((response) => response.blob())
        .then((blob) => {
            const imageUrl = window.URL.createObjectURL(blob);
            const a = document.createElement("a");
            a.href = imageUrl;
            a.download = "imagen.png";
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        })
        .catch((error) => console.error("Error downloading the image:", error));
}
