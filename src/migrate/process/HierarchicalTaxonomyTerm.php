<?php

namespace Drupal\migrate_taxonomy\Plugin\migrate\process;

use Drupal;
use Drupal\migrate\Annotation\MigrateProcessPlugin;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\taxonomy\Entity\Term;
use Drupal\taxonomy\Entity\Vocabulary;

/**
 * This plugin sets missing values on the destination.
 *
 * @MigrateProcessPlugin(
 *   id = "hierarchical_taxonomy_term"
 * )
 */
class HierachicaltTaxonomyTermProcess extends TaxonomyTermProcess {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $this->vocabulary = $this->configuration['default_vocabulary'];
    if (!isset($value) || !$value || is_null($value)) {
      $value = $this->configuration['default_term'];
    }

    $terms_names = explode('|', $value);
    if (!empty($terms_names)) {
      $term = $this->getTerm($terms_names, $this->configuration['default_vocabulary']);
      return Term::load($term->tid);
    }
    else {
      return FALSE;
    }
  }

  public function getTerm($terms_name, $vid) {
    $termsTree = $this->getTermTree();
    for ($i = 0; $i < count($terms_name); $i++) {
      if ($i > 0) {
        $terms_name[$i] = $this->getTermObject($terms_name[$i],  $terms_name[$i-1]->tid);
      }
      else {
        $terms_name[$i] = $this->getTermObject($terms_name[$i]);
      }

    }
    return array_pop($terms_name);
  }
}
