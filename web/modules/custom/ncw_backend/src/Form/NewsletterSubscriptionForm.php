<?php

namespace Drupal\ncw_backend\Form;

use Drupal\Component\Utility\EmailValidatorInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use GuzzleHttp\Client;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a custom form to subscribe for newsletter.
 *
 * @package Drupal\ncw_backend\Form
 */
class NewsletterSubscriptionForm extends FormBase {

  /**
   * @var \Drupal\Component\Utility\EmailValidatorInterface
   */
  protected $emailValidator;

  /**
   * @var \GuzzleHttp\Client
   */
  private $httpClient;

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('email.validator'),
      $container->get('http_client')
    );
  }

  public function __construct(EmailValidatorInterface $emailValidator, Client $httpClient) {
    $this->emailValidator = $emailValidator;
    $this->httpClient = $httpClient;
  }

  /**
   * @inheritDoc
   */
  public function getFormId() {
    return 'newsletter_subscription_form';
  }

  /**
   * @inheritDoc
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['mail'] = [
      '#type' => 'email',
      '#placeholder' => $this->t('E-mail address'),
      '#required' => TRUE,
    ];

    $form['privacy_check'] = [
      '#type' => 'checkbox',
      '#required' => TRUE,
      '#title' => $this->t('I agree to the processing of my personal data'),
    ];

    $form['subscribe'] = [
      '#type' => 'submit',
      '#value' => $this->t('Subscribe'),
    ];
    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    $mail = $form_state->getValue('mail');
    if (!$mail || !$this->emailValidator->isValid($mail)) {
      $form_state->setErrorByName('mail', $this->t('Wrong email introduced'));
    }
    $privacyCheck = $form_state->getValue('privacy_check');
    if (!$privacyCheck) {
      $form_state->setErrorByName('privacy_check', $this->t('Privacy check should be checked to subscribe'));
    }

  }

  /**
   * @inheritDoc
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // @todo Refactor this to get it from config.
    $apiSubscriptionUrl = 'https://jira.osha.europa.eu:8083/OshaService/OSHAService.svc/subscriber?email=' . $form_state->get('mail') . '&type=OSH';
    $response = $this->httpClient->get($apiSubscriptionUrl);
    if ($response->getStatusCode() == 200) {
      $this->messenger()->addMessage($this->t('You should receive a confirmation email in your inbox in the coming minutes. Otherwise, please check your spam folder. Thanks a lot for subscribing.'));
    }

  }

}
