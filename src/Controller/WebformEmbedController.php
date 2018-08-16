<?php

namespace Drupal\webform_embed\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class WebformEmbedController.
 */
class WebformEmbedController extends ControllerBase {

  protected $entityManager;

  protected $webform;

  protected $response;

  protected $loggerDblog;

  /**
   * WebformEmbedController constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_dblog
   */
  public function __construct(EntityTypeManagerInterface $entity_manager, LoggerChannelFactoryInterface $logger_dblog) {
    $this->entityManager = $entity_manager;
    $this->loggerDblog = $logger_dblog;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager'),
      $container->get('logger.factory')
    );
  }

  /**
   * @param $webform
   *
   * @return array
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function displayForm($webform) {

    $form = $this->loadForm($webform);
    if ($form) {
      $output = $this->renderForm($form);
    }
    else {
      $output = "No webform to display.";
    }

    return [
      '#theme' => 'page__webform_embed',
      '#webform_output' => $output,
    ];

  }


  /**
   * @param $machineName
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function loadForm($machineName) {
    try {
      $form = $this->entityManager
        ->getStorage('webform')
        ->load($machineName);
      return $form;
    }
    catch (RequestException $e) {
      $this->loggerDblog->error('Webform failed to load ::' . $e->getMessage());
    }
    return NULL;
  }

  /**
   * @param $form
   *
   * @return array
   */
  protected function renderForm($form) {
    $output = $this->entityManager
      ->getViewBuilder('webform')
      ->view($form);
    return $output;
  }

}
