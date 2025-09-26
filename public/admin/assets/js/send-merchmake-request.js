$(document).on('click', '.item-send-to-merchmake', function() {
    var URL = $(this).attr('data-url');
    Swal.fire({
        title: "Are you sure?",
        text: "You want to send return request in Merchmake Support?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, send it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                url: URL,
                type: "GET",
                success: function(response) {
                    console.log(response);
                    if (response.status === 'success') {
                        Swal.fire({
                            title: 'Sent!',
                            text: 'Return Request sent successfully.',
                            icon: 'success',
                            showConfirmButton: true, // Show confirm button to let the user close the dialog
                        }).then((result) => {
                            if (result.isConfirmed) {
                                var tableId = response.table;
                                var tableInstance = $('#' + tableId).DataTable();
                                tableInstance.ajax.reload();
                                // window.location.reload(); // Reload the page
                            }
                        });
                    } else if (response.status === 'error') {
                        console.log("something went wrong");
                    }
                },
                error: function(xhr, status, error) {
                    console.log(xhr.responseText);
                }
            });
        }
    });
})

$(document).on('click', '.refund-to-user', function() {
    var URL = $(this).attr('data-url');
    Swal.fire({
        title: "Are you sure?",
        text: "You want to Refund?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                url: URL,
                type: "GET",
                success: function(response) {
                    console.log(response.status);
                    if (response.status === 'success') {
                        Swal.fire({
                            title: 'Sent!',
                            text: 'Return Request sent successfully.',
                            icon: 'success',
                            showConfirmButton: true,
                        }).then((result) => {
                            if (result.isConfirmed) {
                                var tableId = response.table;
                                var tableInstance = $('#' + tableId).DataTable();
                                tableInstance.ajax.reload();
                            }
                        });
                    } else if (response.status === 'error') {
                        Swal.fire({
                            title: 'Error!',
                            text: response.message,
                            icon: 'error',
                            showConfirmButton: true,
                        });
                    }
                },
                error: function(xhr, status, error) {
                    var errorMessage = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'An error occurred, please try again later.';

                    Swal.fire({
                        title: 'Error!',
                        text: errorMessage,
                        icon: 'error',
                        showConfirmButton: true,
                    });
                }
            });
        }
    });
})
