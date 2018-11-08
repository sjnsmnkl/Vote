<?php
/**
 * @file
 * Contains \Drupal\vote_for_event\Form\voteForm.
 */

namespace Drupal\vote_for_event\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Database;

/**
 * Defines a form.
 */
class VoteForm extends FormBase{
    
    /**
     * {@inherithoc}
     */
    public function getFormId(){
        return 'vote_id';
    }
    
    /**
     * {@inherithoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state){

        $form['event'] = array(
            '#type' => 'select',
            '#title' => $this->t('Select an event:'),
            '#options' => array(t('Choose an event'), t('Music Event'), t('Sport Event'), t('Party Event')),
        );

        
        $form['city'] = array(
            '#type' => 'select',
            '#title' => $this->t('Select a city:'),
            '#options' => array(t('Choose a city'), t('London'), t('Toronto'), t('Niagara'))
        );
        
        $form['name'] = array(
            '#type' => 'textfield',
            '#title' => $this->t('Your name:')
        );
        
        $form['email'] = array(
            '#type' => 'email',
            '#title' => $this->t('Your email:')
        );
        
        $form['vote'] = array(
            '#type' => 'submit',
            '#value' => $this->t('Vote')
        );
        
        
        
        return $form;
    }
    
    /**
     * {@inheritdoc}
     */
    public function validateForm(array &$form, FormStateInterface $form_state) {
        if($form_state->getValue('event') == 0 || $form_state->getValue('city') == 0) {
             $form_state->setErrorByName('Missing choice', t('Please complete the choices.'));
        }
        
        if(strlen($form_state->getValue('name')) == 0) {
             $form_state->setErrorByName('Warning', t('Please input your name.'));
        }
        
        if (!preg_match("/^[a-zA-Z ]*$/", $form_state->getValue('name'))) {
            $form_state->setErrorByName('Warning', t('Name field can only contain letters and spaces.'));
        } 
 
        if(strlen($form_state->getValue('email')) == 0) {
             $form_state->setErrorByName('Warning', t('Please input your email.'));
           }
    }
    
    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) { 
        $name = $form_state->getValue('name');
        $email = $form_state->getValue('email');
        $eventKey = $form_state->getValue('event');
        if($eventKey == 1) {
            $eventOption = 'music';
        }
        else if($eventKey == 2) {
            $eventOption = 'sport';
        }
        else if($eventKey == 3) {
            $eventOption = 'party';
        }
        //$eventOption = &$form['event']['#options'][$form_state['values']['event']];
        
        $cityKey = $form_state->getValue('city');
        if($cityKey == 1) {
            $cityOption = 'London';
        }
        else if($cityKey == 2) {
            $cityOption = 'Toronto';
        }
        else if($cityKey == 3) {
            $cityOption = 'Niagara';
        }

        //insert into database
        $insertQuery = db_query("INSERT INTO {vote} (name, email, event, city) VALUES (?, ?, ?, ?)", array($name, $email, $eventOption, $cityOption));
        
        //count results
        $connection = Database::getConnection();
        $countLonon = 0;
        $options = array();
        $resultLondon = $connection->query('SELECT * FROM vote WHERE city = :city AND event = :event', array(':city' => 'London', ':event' => $eventOption), $options);

        foreach($resultLondon as $item) {
            $countLondon++;
        }
        
        $countToronto = 0;
        //$options = array();
        $resultToronto = $connection->query('SELECT * FROM vote WHERE city = :city AND event = :event', array(':city' => 'Toronto', ':event' => $eventOption), $options);

        foreach($resultToronto as $item) {
            $countToronto++;
        }
        
        $countNiagara = 0;
        
        $options = array();
        $resultNiagara = $connection->query('SELECT * FROM vote WHERE city = :city AND event = :event', array(':city' => 'Niagara', ':event' => $eventOption), $options);

        foreach($resultNiagara as $item) {
            $countNiagara++;
        }

        //ranking
        $countArray = array(
            'London' => $countLondon,
            'Toronto' => $countToronto,
            'Niagara' => $countNiagara
        );
        
        arsort($countArray);
        
        $keys = array_keys($countArray);
        $values = array_values($countArray);
        
        //show thank you info and ranking result
        $thanks = "Thank you for voting, ". $name. "!";
        $voted = "You voted for next year's ". $eventOption. " event to take place in ". $cityOption. ".";
        $result = "Here are the current rankings for ".     $eventOption. ":";
        drupal_set_message($thanks);
        drupal_set_message($voted);
        drupal_set_message($result);

        drupal_set_message(t('1. @keys.', array('@keys' => $keys[0])), 'status');
        drupal_set_message(t('2. @keys.', array('@keys' => $keys[1])), 'status');
        drupal_set_message(t('2. @keys.', array('@keys' => $keys[2])), 'status');
    }
}
?>