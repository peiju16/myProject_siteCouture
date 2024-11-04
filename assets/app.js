import './bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.css';

console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉');

$(document).ready(function () {
    let itemId;
    let itemName; 

    // Store the item ID when the delete button is clicked
    $(document).on('click', '.delete-button', function () {
        itemId = $(this).data('id');
        itemName = $(this).data('name');

        // Update the modal title and body content
        $('#deleteModal .modal-body').html(`<p>Êtes-vous sûr de vouloir supprimer le produit "${itemName}" ?</p>`);
    });
    // Handle the confirmation click in the modal
    $(document).on('click', '.confirm-delete', function () {
        if (itemId) {
            $.ajax({
                url: `/product/delete/${itemId}`,
                type: 'POST',
                success: function (response) {
                    if (response.status === 'success') {

                        // Redirect to the product list page if a redirect URL is provided
                        if (response.redirect) {
                            window.location.href = response.redirect;
                        } else {
                            // Optionally reload the page to reflect the deletion
                            location.reload();
                        }
                    } else {
                        alert('Erreur: ' + response.message);
                    }

                    // Close the modal
                    $('#deleteModal').modal('hide');
                },
                error: function (xhr) {
                    // Display the error message in the modal box
                    $('#deleteModal .modal-body').html('<p class="text-danger">Échec de la suppression de l\'élément. Veuillez réessayer.</p>');
                }
            });
        }
    });
});

$(document).ready(function() {
    // Set default image to the first thumbnail
    const defaultImageSrc = $('.thumbnail').first().attr('src');
    $('#main-image').attr('src', defaultImageSrc);

    // Update main image on thumbnail click
    $('.thumbnail').on('click', function() {
        const newSrc = $(this).attr('src');
        $('#main-image').attr('src', newSrc);
    });
});





