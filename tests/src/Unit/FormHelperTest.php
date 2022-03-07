<?php

namespace Drupal\Tests\pco_cities\Unit;

use Prophecy\PhpUnit\ProphecyTrait;
use Drupal\Core\Render\ElementInfoManagerInterface;
use Drupal\pco_cities\FormHelper;
use Drupal\Tests\UnitTestCase;

/**
 * @group pco_cities
 *
 * @coversDefaultClass \Drupal\pco_cities\FormHelper
 */
class FormHelperTest extends UnitTestCase {

  use ProphecyTrait;
  /**
   * @covers ::applyStandardProcessing
   */
  public function testApplyStandardProcessing() {
    $element_info = $this->prophesize(ElementInfoManagerInterface::class);
    $element_info->getInfo('location')->willReturn([
      '#process' => [
        'process_location',
      ],
    ]);
    $element = ['#type' => 'location'];

    $form_helper = new FormHelper($element_info->reveal());
    $form_helper->applyStandardProcessing($element);

    $this->assertEquals(['process_location'], $element['#process']);
  }

}
