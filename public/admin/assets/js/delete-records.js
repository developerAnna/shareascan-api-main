$(document).on('click', '.item-delete', function() {
    var URL = $(this).attr('data-url');

    var deletetype = $(this).attr('data-type');
    var textmsj = "You won't be able to revert this!";
    if(deletetype != '' && deletetype == 'order'){
        textmsj = "You want to delete this record!";
    }

    Swal.fire({
        title: "Are you sure?",
        text: textmsj,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                url: URL,
                type: "DELETE",
                success: function(response) {
                    console.log(response);
                    if (response.status === 'success') {
                        Swal.fire({
                            title: 'Deleted!',
                            text: 'Record has been deleted successfully.',
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


$(document).on('click', '.item-restore', function() {
    var URL = $(this).attr('data-url');
    Swal.fire({
        title: "Are you sure?",
        text: "You want to restore the record?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, restore it!'
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
                            title: 'Restored!',
                            text: 'Record has been restored successfully.',
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
