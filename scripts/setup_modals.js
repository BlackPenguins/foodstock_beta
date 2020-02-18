function setupModal( modalType ) {
    var modalButton = document.getElementById(modalType + "_button");
    var modal = document.getElementById(modalType + "_modal");
    var modalCloseButton = document.getElementById(modalType + "_close_button");

    //--------------------------------------------------------------------------------
    // When the user clicks on the button, open the modal
    //--------------------------------------------------------------------------------
    if( modalButton != null && modalType != "payment" ) {
        modalButton.onclick = function () {
            modal.style.display = "block";
        };
    }

    //--------------------------------------------------------------------------------
    // When the user clicks on <span> (x), close the modal
    //--------------------------------------------------------------------------------
    if( modalCloseButton != null ) {
        modalCloseButton.onclick = function () {
            modal.style.display = "none";
        };
    }
}

function openPaymentModal( user, userID, month, method, sodaAmount, snackAmount, sodaCommission, snackCommission ) {
        $('#payment_modal').show();
        $('#UserIDLabel').html(user);
        $('#UserID').val(userID);
        $('#Month').val(month);
        $('#MonthLabel').html(month);
        $('#SodaAmount').val(sodaAmount);
        $('#SnackAmount').val(snackAmount);
        $('#SodaUnpaid').val(sodaAmount);
        $('#SnackUnpaid').val(snackAmount);
        $('#SodaCommission').val(sodaCommission);
        $('#SnackCommission').val(snackCommission);

        $totalAmount = ( sodaAmount + snackAmount ).toFixed( 2 );
        console.log("So [" + sodaAmount + "] Sn [" + snackAmount + "] Tot [" + $totalAmount + "]" );
        $('#TotalAmount').val($totalAmount);

        if( sodaCommission > 0 || snackCommission > 0 ) {
            $("#TotalAmount").prop('disabled', true);
        } else {
            $("#TotalAmount").prop('disabled', false);
        }
    }