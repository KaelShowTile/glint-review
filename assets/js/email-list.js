jQuery(document).ready(function($){

    $(".delete-email-button").on("click", function() {
        var emailId = $(this).data("email-id");
        var button = $(this);

        Swal.fire({
            title: "Are you sure?",
            text: "You won\'t be able to revert this!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, delete it!"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: ajaxurl,
                    type: "POST",
                    data: {
                        action: "delete_email",
                        email_id: emailId
                    },
                    success: function(response) {
                        if (response.success) {
                            // Remove the row from the table
                            $("#email-row-" + emailId).remove();
                            Swal.fire({
                                icon: "success",
                                title: "Deleted!",
                                text: "The scheduled email has been deleted.",
                                toast: true,
                                position: "top-end",
                                showConfirmButton: false,
                                timer: 3000
                            });
                        } else {
                            Swal.fire({
                                icon: "error",
                                title: "Error",
                                text: "Failed to delete the email.",
                                toast: true,
                                position: "top-end",
                                showConfirmButton: false,
                                timer: 3000
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: "AJAX request failed. Please try again.",
                            toast: true,
                            position: "top-end",
                            showConfirmButton: false,
                            timer: 3000
                        });
                    }
                });
            }
        });
    });

})