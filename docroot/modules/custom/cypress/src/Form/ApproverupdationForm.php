<?php
namespace Drupal\cypress\Form;

use Drupal\Core\Database\Database;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\eck\Entity;


class ApproverupdationForm extends FormBase
{
    /**
     * {@inheritdoc}
     */
    public function getFormId()
    {
        return 'approver_updation_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $form['approver_name'] = array(
            '#type' => 'textfield',
            '#title' => t('Approver'),
            '#required' => TRUE,
        );
        $form['approver_from_date'] = array(
            '#type' => 'date',
            '#title' => t('From'),
            '#required' => TRUE,
        );
        $form['approver_to_date'] = array(
            '#type' => 'date',
            '#title' => t('To'),
            '#required' => TRUE,
        );
        $form['actions']['submit'] = array(
            '#type' => 'submit',
            '#value' => $this->t('Save'),
        );
        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function validateForm(array &$form, FormStateInterface $form_state) {
        $name = $form_state->getValue('approver_name');
        $from_date = strtotime($form_state->getValue('approver_from_date'));
        $to_date = strtotime($form_state->getValue('approver_to_date'));
        //Query to fetch if the name is in the user table.
        $query = \Drupal::database()->select('users_field_data', 'ufd');
        $query->fields('ufd',array('name'));
        $query->condition('ufd.mail', $name.'@%', 'LIKE');
        $user_name = $query->execute()->fetchAll();
        if(empty($user_name)) {
            $form_state->setErrorByName('approver_name', $this->t('User is invalid'));
        }
        if($from_date > $to_date) {
            $form_state->setErrorByName('approver_from_date', $this->t('From date cannot be greater than To date'));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {
        $name = $form_state->getValue('approver_name');
        $from_date = $form_state->getValue('approver_from_date');
        $from_date_format = date("Y-m-d H:i:s",strtotime($from_date));
        $to_date = $form_state->getValue('approver_to_date');
        $to_date_format = date("Y-m-d H:i:s",strtotime($to_date));

        //Query to fetch entity id's for the approver.
        $query = \Drupal::database()->select('shope_code_approvers__2ac328e2fb', 'sca');
        $query->join('user__field_legacy_uid', 'ufl', 'sca.field_shopcodeprimaryapprover_value=ufl.field_legacy_uid_value');
        $query->fields('sca', array('entity_id', 'field_shopcodeprimaryapprover_value'));
        $query->join('users_field_data', 'ufd', 'ufl.entity_id=ufd.uid');
        $query->condition('ufd.mail', $name.'@%', 'LIKE');
        $ids = $query->execute()->fetchAll();
        if(!empty($ids)) {
            foreach ($ids as $id) {
                $entity_id = $id->entity_id;
                $entity_object = Entity\EckEntity::load($entity_id);
                $entity_object->set('field_primaryapproverleavefrom', $from_date_format);
                $entity_object->set('field_primaryapproverleaveto', $to_date_format);
                $entity_object->save();
            }
            $message = t('The dates have been updated successfully.') ;
            drupal_set_message($message);
        }
        else {
            $message = t('The user is not an approver.') ;
            drupal_set_message($message);
        }
    }
}

