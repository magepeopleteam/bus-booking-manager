<?php
<<<<<<< HEAD

=======
>>>>>>> f29f306563aaedc2b09b51a8cc65c59109e1c9aa

add_action('wbbm_prevent_form_resubmission', 'wbbm_prevent_form_resubmission_fun');
function wbbm_prevent_form_resubmission_fun()
{
    ?>
    <script>
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>

<?php } ?>
