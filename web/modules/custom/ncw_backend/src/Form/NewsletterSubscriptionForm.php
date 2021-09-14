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

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('email.validator'),
      $container->get('http_client'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(EmailValidatorInterface $emailValidator, Client $httpClient) {
    $this->emailValidator = $emailValidator;
    $this->httpClient = $httpClient;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'newsletter_subscription_form';
  }

  /**
   * {@inheritdoc}
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

  /**
   * {@inheritdoc}
   */
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
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $response = NULL;
    $config = $this->config('ncw_backend.settings');
    $apiSubscriptionUrl = $config->get('newsletter_api');
    $apiSubscriptionUrl = str_replace('{email}', $form_state->getValue('mail'), $apiSubscriptionUrl);
    try {
      $response = $this->httpClient->get($apiSubscriptionUrl);
    }
    catch (\Exception $exception) {
      $this->messenger()->addError($this->t('Error sending subscription. Please try again later.'));
      $this->logger('ncw_backend')->error("Error sending newsletter subscription with error @error and stacktrace @trace", [
        '@error' => $exception->getMessage(),
        '@trace' => $exception->getTraceAsString(),
      ]);
    }
    if ($response) {
      $statusCode = $response->getStatusCode();
      if ($statusCode == 200) {
        $this->messenger()
          ->addMessage($this->t('You should receive a confirmation email in your inbox in the coming minutes. Otherwise, please check your spam folder. Thanks a lot for subscribing.'));
      }
      else {
        $this->messenger()->addError($this->t('Error sending subscription. Please try again later.'));
        $this->logger('ncw_backend')->error('Error sending subscription with code %code', [
          '%code' => $statusCode,
        ]);
      }
    }
    else {
      $this->logger('ncw_backend')->error("Empty response calling api on url: %url", [
        '%url' => $apiSubscriptionUrl,
      ]);
      $this->messenger()->addError($this->t('Empty response calling api'));
    }

  }

}
