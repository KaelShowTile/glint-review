jQuery(document).ready(function($) {
    // Handle Show Review checkbox change
    $(".show-review-checkbox").on("change", function() {
        var reviewId = $(this).data("review-id");
        var isChecked = $(this).is(":checked") ? 1 : 0;

        $.ajax({
            url: ajaxurl,
            type: "POST",
            data: {
                action: "update_show_review",
                review_id: reviewId,
                show_review: isChecked
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: "success",
                        title: "Success",
                        text: "Show Review updated successfully!",
                        toast: true,
                        position: "top-end",
                        showConfirmButton: false,
                        timer: 3000
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "Failed to update Show Review.",
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
    });

    // Handle Delete button click
    $(".delete-review-button").on("click", function() {
        var reviewId = $(this).data("review-id");
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
                        action: "delete_review",
                        review_id: reviewId
                    },
                    success: function(response) {
                        if (response.success) {
                            // Remove the row from the table
                            $("#review-row-" + reviewId).remove();
                            Swal.fire({
                                icon: "success",
                                title: "Deleted!",
                                text: "The review has been deleted.",
                                toast: true,
                                position: "top-end",
                                showConfirmButton: false,
                                timer: 3000
                            });
                        } else {
                            Swal.fire({
                                icon: "error",
                                title: "Error",
                                text: "Failed to delete the review.",
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
});