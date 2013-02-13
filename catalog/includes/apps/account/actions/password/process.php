<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class app_account_action_password_process {
    public static function execute(app $app) {
      global $OSCOM_PDO, $messageStack;

      if ( isset($_POST['formid']) && ($_POST['formid'] == $_SESSION['sessiontoken']) ) {
        $password_current = isset($_POST['password_current']) ? trim($_POST['password_current']) : null;
        $password_new = isset($_POST['password_new']) ? trim($_POST['password_new']) : null;
        $password_confirmation = isset($_POST['password_confirmation']) ? trim($_POST['password_confirmation']) : null;

        $error = false;

        if ( strlen($password_new) < ENTRY_PASSWORD_MIN_LENGTH ) {
          $error = true;

          $messageStack->add('account_password', ENTRY_PASSWORD_NEW_ERROR);
        } elseif ( $password_new != $password_confirmation ) {
          $error = true;

          $messageStack->add('account_password', ENTRY_PASSWORD_NEW_ERROR_NOT_MATCHING);
        }

        if ( $error === false ) {
          $Qpw = $OSCOM_PDO->prepare('select customers_password from :table_customers where customers_id = :customers_id');
          $Qpw->bindInt(':customers_id', $_SESSION['customer_id']);
          $Qpw->execute();

          if ( tep_validate_password($password_current, $Qpw->value('customers_password')) ) {
            $OSCOM_PDO->perform('customers', array('customers_password' => tep_encrypt_password($password_new)), array('customers_id' => $_SESSION['customer_id']));

            $OSCOM_PDO->perform('customers_info', array('customers_info_date_account_last_modified' => 'now()'), array('customers_info_id' => $_SESSION['customer_id']));

            $messageStack->add_session('account', SUCCESS_PASSWORD_UPDATED, 'success');

            tep_redirect(tep_href_link('account', '', 'SSL'));
          } else {
            $error = true;

            $messageStack->add('account_password', ERROR_CURRENT_PASSWORD_NOT_MATCHING);
          }
        }
      }
    }
  }
?>
