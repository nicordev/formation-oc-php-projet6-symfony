
// Image manager

function ImageManager (uploadUrl, deleteUrl, uploadInputElement) {

    let imageManager = {

        uploadInputElt: uploadInputElement,

        init: () => {

            imageManager.uploadInputElt.addEventListener("change", imageManager.upload);
        },

        /**
         * Upload the image file
         */
        upload: () => {

            var xhr = new XMLHttpRequest(),
                form = new FormData();

            form.append(imageManager.uploadInputElt.name, imageManager.uploadInputElt.files[0]);
            xhr.open("POST", uploadUrl);
            xhr.addEventListener("readystatechange", () => {

                if (xhr.readyState === XMLHttpRequest.DONE) {
                    if (xhr.status === 200) {
                        let imageUrl = JSON.parse(xhr.responseText);

                        if (imageUrl) {
                            let imageInputElt = myTrickEditor.addInput('trick_images', true);
                            imageInputElt.value = imageUrl;
                            imageLibrary.addImage(imageUrl, imageInputElt);
                            console.log("Image successfully uploaded");

                        } else {
                            console.log("Error when uploading an image on the server");
                        }

                    } else {
                        console.log("Error with AJAX request to load an image: " + xhr.status);
                    }
                }
            });

            xhr.send(form);
        },

        /**
         * Delete the image file on the server
         */
        delete: (filePath) => {

            let xhr = new XMLHttpRequest(),
                form = new FormData();

            form.append("imageUrl", filePath);
            xhr.open("POST", deleteUrl);
            xhr.addEventListener("readystatechange", () => {

                if (xhr.readyState === XMLHttpRequest.DONE) {
                    if (xhr.status === 200) {
                        console.log("Image " + filePath + " deleted");

                    } else {
                        console.log("Error " + xhr.status + " with AJAX request to delete image: " + filePath);
                    }
                }
            });

            xhr.send(form);
        }
    };

    return imageManager;
}

// Image library

function ImageLibrary(libraryElement, mainImageInputElement, mainImageClass) {

    let imageLibrary = {

        element: libraryElement,
        mainImageInputElement: mainImageInputElement,
        mainImageClass: mainImageClass,

        addImage: (imageUrl, imageInputElt) => {

            let figureElt = document.createElement("figure"),
                deleteElt = document.createElement("figcaption"),
                imageElt = document.createElement("img");

            imageElt.src = imageUrl;
            imageElt.alt = "Image de snowboard";
            figureElt.classList.add("image-library-element");
            figureElt.appendChild(imageElt);
            deleteElt.textContent = "×";
            deleteElt.classList.add("image-library-element-delete");
            figureElt.appendChild(deleteElt);
            imageLibrary.element.appendChild(figureElt);

            // Delete image on click
            deleteElt.addEventListener("click", () => {

                imageManager.delete(imageUrl);
                if (imageLibrary.mainImageInputElement.value.includes(imageUrl)) {
                    imageLibrary.mainImageInputElement.value = "";
                }
                figureElt.remove();
                imageInputElt.parentElement.parentElement.parentElement.remove();
            });

            // Select this image as main image on click
            imageElt.addEventListener("click", () => {

                imageLibrary.setAsMainImage(imageElt);
            });

            return imageElt;
        },

        setAsMainImage: (imageElt) => {

            let imgElements = document.getElementsByTagName("img");

            for (let i = 0, size = imgElements.length; i < size; i++) {
                if (imgElements[i].classList.contains(imageLibrary.mainImageClass)) {
                    imgElements[i].classList.remove(imageLibrary.mainImageClass);
                }
            }

            imageElt.classList.add(imageLibrary.mainImageClass);
            imageLibrary.mainImageInputElement.value = imageElt.src;
        }
    };

    return imageLibrary;
}
