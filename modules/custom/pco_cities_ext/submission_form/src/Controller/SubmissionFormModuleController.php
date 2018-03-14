<?php

namespace Drupal\submission_form_module\Controller;

use Drupal\Core\Path\AliasManagerInterface;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class SubmissionFormModuleController extends ControllerBase {
  /**
   * The path alias manager.
   *
   * @var \Drupal\Core\Path\AliasManagerInterface
   */
  private $aliasManager;

  /**
   * Constructs a SubmissionFormModuleController object.
   *
   * @param \Drupal\Core\Path\AliasManagerInterface $aliasManager
   *   The path alias manager.
   */
  public function __construct(AliasManagerInterface $aliasManager) {
    $this->aliasManager = $aliasManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('path.alias_manager')
    );
  }

  public function submissionSuccessPage($challenge, Request $request) {

    $challenge_slug = $request->get('challenge');
    $path = $this->aliasManager->getPathByAlias('/challenges/' . $challenge_slug);

    if (preg_match('/node\/(\d+)/', $path, $matches)) {
      $node_storage = $this->entityTypeManager()->getStorage('node');

      $node = $node_storage->load($matches[1]);
    }
    else {
      throw new NotFoundHttpException();
    }

    $page['#theme'] = 'submission_form_module_page_theme';
    $page['#attached']['library'][] = 'submission_form_module/submission-form';

    $page['#challenge_name'] = $node->title->value;
    $page['#challenge_department'] = $node->get('field_challenge_department')->getValue()[0]['value'];
    $page['#challenge_image'] = file_create_url($node->field_challenge_image->entity->uri->value);

    $page['#submission_success'] = TRUE;
    $page['#submission_email'] = $request->get('email');

    return $page;
  }

  public function submissionFormPage($challenge, Request $request) {
    $challenge_slug = $request->get('challenge');
    $submission_error = $request->get('error');

    $path = $this->aliasManager->getPathByAlias('/challenges/' . $challenge_slug);

    if (preg_match('/node\/(\d+)/', $path, $matches)) {
      $node_storage = $this->entityTypeManager()->getStorage('node');

      $node = $node_storage->load($matches[1]);
    }
    else {
      throw new NotFoundHttpException();
    }

    $form = $this->formBuilder()->getForm('Drupal\submission_form_module\Form\SubmissionForm');

    // Wrap the theme with WET4 validation tag.
    $form['#prefix'] = '<div class="wb-frmvld">';
    $form['#suffix'] = '</div>';

    $form['#theme'] = 'submission_form_module_page_theme';
    $form['#attached']['library'][] = 'submission_form_module/submission-form';

    $form['#challenge_name'] = $node->title->value;
    $form['#challenge_department'] = $node->get('field_challenge_department')->getValue()[0]['value'];
    $form['#challenge_image'] = file_create_url($node->field_challenge_image->entity->uri->value);

    $form['#submission_error'] = $submission_error;

    return $form;
  }

}