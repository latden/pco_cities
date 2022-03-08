<?php

use Drupal\DrupalExtension\Context\MinkContext;
/**
 * @file
 * Contains \PCOCITIESSubContext.
 */

use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Mink\Exception\ElementNotFoundException;
use Behat\Mink\Exception\ExpectationException;
use Behat\Mink\Exception\UnsupportedDriverActionException;
use Drupal\DrupalExtension\Context\DrupalSubContextBase;

/**
 * Sub context for PCO CITIES step definitions.
 */
class PCOCITIESSubContext extends DrupalSubContextBase {

  /**
   * The Mink context.
   *
   * @var MinkContext
   */
  protected $minkContext;

  /**
   * Start time for each scenario.
   */
  protected $startTime;

  /**
   * Pre-scenario hook.
   *
   * @BeforeScenario
   */
  public function gatherContexts(BeforeScenarioScope $scope) {
    $environment = $scope->getEnvironment();

    $this->minkContext = $environment->getContext('Drupal\DrupalExtension\Context\MinkContext');
  }

  /**
   * Store current time.
   *
   * @BeforeScenario ~@errors
   */
  public function setStartTime() {
    $this->startTime = time();
  }

  /**
   * Check for errors since the scenario started.
   *
   * @AfterScenario ~@errors
   */
  public function checkWatchdog(AfterScenarioScope $scope) {
    if (db_table_exists('watchdog')) {
      // TODO: Drupal Rector Notice: Please delete the following comment after you've made any necessary changes.
      // You will need to use `\Drupal\core\Database\Database::getConnection()` if you do not yet have access to the container here.
      $log = \Drupal::database()->select('watchdog', 'w')
        ->fields('w')
        ->condition('w.type', 'php', '=')
        ->condition('w.timestamp', $this->startTime, '>=')
        ->execute()
        ->fetchAll();
      if (!empty($log)) {
        foreach ($log as $error) {
          // Make the substitutions easier to read in the log.
          $error->variables = unserialize($error->variables);
          print_r($error);
        }
        throw new \Exception('PHP errors logged to watchdog in this scenario.');
      }
    }
  }

}
