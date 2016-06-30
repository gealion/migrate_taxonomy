<?php


namespace Drupal\migrate_taxonomy\Plugin\migrate\process;
use Drupal\migrate\Annotation\MigrateProcessPlugin;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use Drupal\taxonomy\Entity\Term;

/**
 * This plugin sets missing values on the destination.
 *
 * @MigrateProcessPlugin(
 *   id = "taxonomy_term"
 * )
 */
class TaxonomyTermProcess extends ProcessPluginBase {

  protected $vocabulary;

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $this->vocabulary = $this->configuration['default_vocabulary'];
    if (!isset($value) || !$value || is_null($value)) {
      $value = $this->configuration['default_term'];
    }

    if ($term = $this->getTermObject($value)) {
      return $term;
    }
    else {
      return $this->createTerm($value);
    }
  }

  /**
   * Get the full tree of terms for a given Vocuabulary Id
   * @param $vid String Vocabulary ID
   */
  protected function getTermTree() {
    $container = \Drupal::getContainer();
    $termsTree = $container->get('entity.manager')->getStorage('taxonomy_term')->loadTree($this->vocabulary);
    return $termsTree;
  }

  /**
   * Get Term by Name in givent Taxonomy Tree
   * @param $termName
   * @param null $parent
   * @return mixed
   */
  function getTermObject($termName, $parent = NULL) {
    $termsTree = $this->getTermTree();
    foreach($termsTree as $key => $term) {
      if ( $term->name == $termName) {
        if ($parent == NULL) {
          return $term;
        }
        else {
          if ($term->parents[0] == $parent) {
            return $term;
          }
        }
      }
    }
    return FALSE;
  }

  /**
   * Add Term
   * @param $termName
   * @return int
   */
  function createTerm($termName) {
    $term = Term::create([
      'name' => $termName,
      'vid' => $this->vocabulary,
    ])
      ->save();
    return $term;
  }
}