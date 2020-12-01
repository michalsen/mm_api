<?php
/**
  * @file
  * Contains \Drupal\CNCExSf\Form\CNCExSfForm
  */

namespace Drupal\MMApi\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Render\Markup;

use Drupal\node\Entity\Node;
use Drupal\field\FieldConfigInterface;

/**
  *  Provides SNTrack Email form
  */
class MMApiForm extends FormBase {

  public function getFormId() {
    return 'SNTrack_email_form';
  }

  /**
    * (@inheritdoc)
    */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $cncexmm = \Drupal::state()->get('cncexmm');
    $mmCred = json_decode($cncexmm);

        $form['cncexmm_api'] = array(
          '#title' => t('Machinery Manager API'),
          '#type' => 'textfield',
          '#default_value' => $mmCred->cncexmm_api,
          '#required' => TRUE
        );

    $form['submit'] = array(
        '#type' => 'submit',
        '#value' => t('Submit')
      );

    $mmfields = getmmfields($mmCred->cncexmm_api);
    $d8fields = getd8fields($mmCred->cncexmm_api);

    $mmfieldrows = json_decode($mmfields);


    $header = ['field' => 'Field', 'content' => 'Content'];

    foreach($mmfieldrows as $key => $value) {
      foreach ($value as $vkey => $vvalue) {
        foreach($vvalue->attributes as $field => $data) {
           $mmrows[] = [$field, $data];
        }
      }
    }



    $d8rows = [];
    // TEST NODE FOR FIELDS
    $node = Node::load(102976);
    foreach($d8fields as $key => $value) {
      $d8rows[] = [$value, $node->$value->value];
    }


    $build['mmtable'] = [
      '#type' => 'table',
      '#header' => $header,
      '#attributes'=> ['class' => ['cncexlayout']],
      '#rows' => $mmrows,
      '#empty' => t('No content has been found.'),
    ];

    $d8build['d8table'] = [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $d8rows,
      '#attributes'=> ['class' => ['cncexlayout']],
      '#empty' => t('No content has been found.'),
    ];

    // RENDER TABLES
    $form['fieldsmm'] = array(
      '#type' => 'markup',
      '#markup' => render($build),
    );
    $form['fieldsd8'] = array(
      '#type' => 'markup',
      '#markup' => render($d8build),
    );

    return $form;

  }

  /**
    * (@inheritdoc)
    */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $value['cncexmm_api'] = $form_state->getValue('cncexmm_api');
    \Drupal::state()->set('cncexmm', json_encode($value));
  }

}


function MachineryManagerCredentials() {
  $cncexmm = \Drupal::state()->get('cncexmm');
  $mmCred = json_decode($cncexmm);
  return $mmCred;
}

function getmmfields($api) {
  $fields = \Drupal::httpClient()
            ->get('https://www.machinerymanager.com/mm_site_api/assets', [
              'headers' => ['Accept' => 'application/json',
                            'mm-site-api-token' => $api],
            ])
            ->getBody(TRUE);
  return $fields;
}

function getd8fields() {
  $node = Node::load(102976);
  $fields = [];
  foreach($node as $key => $value) {
    $fields[] = $key ;
  }
  return $fields;
}
