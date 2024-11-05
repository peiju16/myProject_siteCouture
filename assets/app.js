import './bootstrap.js';
import './styles/app.css';

console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉');

// Delete Item Logic
function initializeDeleteButton() {
    let itemId, itemName;

    $(document).on('click', '.delete-button', function () {
        itemId = $(this).data('id');
        itemName = $(this).data('name');

        // Update the modal content
        $('#deleteModal .modal-body').html(`<p>Êtes-vous sûr de vouloir supprimer le produit "${itemName}" ?</p>`);
    });

    $(document).on('click', '.confirm-delete', function () {
        if (itemId) {
            $.ajax({
                url: `/product/delete/${itemId}`,
                type: 'POST',
                success: function (response) {
                    if (response.status === 'success') {
                        if (response.redirect) {
                            window.location.href = response.redirect;
                        } else {
                            location.reload();
                        }
                    } else {
                        alert('Erreur: ' + response.message);
                    }
                    $('#deleteModal').modal('hide');
                },
                error: function (xhr) {
                    $('#deleteModal .modal-body').html('<p class="text-danger">Échec de la suppression de l\'élément. Veuillez réessayer.</p>');
                }
            });
        }
    });
}

// Image Gallery Logic
function initializeImageGallery() {
    // Reset default image to the first thumbnail
    const defaultImageSrc = $('.thumbnail').first().attr('src');
    $('#main-image').attr('src', defaultImageSrc);

    // Set up thumbnail click event
    $('.thumbnail').off('click').on('click', function () {
        const newSrc = $(this).attr('src');
        $('#main-image').attr('src', newSrc);
    });
}

// Initialize on document ready
$(document).ready(function() {
    initializeDeleteButton();
    initializeImageGallery();

    // Trigger reinitialization on content updates
    $(document).on('contentUpdated', function() {
        initializeImageGallery();
        initializeDeleteButton();
    });

    // If content is dynamically loaded, manually trigger the custom event
    $(document).trigger('contentUpdated');
});
