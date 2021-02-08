<?php

namespace Drupal\custom_migrate\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Database\Connection;

/**
 * @file
 * Results table where we can check if the migration is working correctly .
 */

/**
 * Controller where we can check if the migration is working correctly .
 */
class MigrateResultController extends ControllerBase {

  /**
   * The database service.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Constructs a ParagraphController object.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   A database connection.
   */
  public function __construct(Connection $database) {
    $this->database = $database;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database')
    );
  }

  /**
   * Obtiene los resultados de las migraciones y los pinta en tablas.
   */
  public function results() {
    $this->resultsTaxonomies();
    $this->resultsContentTypes();
  }

  /**
   * Obtiene los resultados de migrar taxonomias.
   */
  public function resultsTaxonomies() {
    $results = [];
    $languages = ['und', 'es', 'en'];

    // TAXONOMY.
    $taxonomies = [
      0 => [4, 'category'],
      1 => [1, 'section'],
      2 => [6, 'office'],
      3 => [7, 'project_type'],
      4 => [2, 'publication'],
      5 => [9, 'hierarchy'],
      6 => [10, 'press_release_type'],
    ];
    foreach ($taxonomies as $taxonomy) {
      try {

        // Inicialice.
        foreach ($languages as $lang) {
          $results['taxonomy'][$taxonomy[1]]['D7'][$lang] = 0;
          $results['taxonomy'][$taxonomy[1]]['D8'][$lang] = 0;
        }

        // D7.
        $database = 'webree_d7';
        $res = $this->database->query(
          "SELECT language, COUNT(*) AS num FROM $database.taxonomy_term_data WHERE vid='" . $taxonomy[0] . "' GROUP BY language"
          )->fetchAll();
        if ($res) {
          foreach ($res as $val) {
            $results['taxonomy'][$taxonomy[1]]['D7'][$val->language] = $val->num;
          }
        }

        // D8.
        $res = $this->database->query(
            "SELECT langcode AS language, COUNT(*) as num FROM taxonomy_term_data WHERE vid='" . $taxonomy[1] . "' GROUP BY LANGUAGE"
            )->fetchAll();
        if ($res) {
          foreach ($res as $val) {
            $results['taxonomy'][$taxonomy[1]]['D8'][$val->language] = $val->num;
          }
        }
      }
      catch (\Exception $e) {
      }
    }

    $table = $this->tableTaxonomiesResults($results);
    print_r($table); echo "<br><br>";
  }

  /**
   * Obtiene los resultados de migrar content types.
   */
  public function resultsContentTypes() {
    // Inicialice.
    $results = [];
    $languages = ['und', 'es', 'en'];

    // CONTENT TYPES.
    $content_types = [
      0 => ['events', 'event'],
      1 => ['office', 'office'],
      2 => ['charts', 'charts'],
      3 => ['manager', 'manager'],
      4 => ['press_release', 'press_release'],
      5 => [['publication_list', 'page'], 'page'],
      6 => ['publication', 'publication'],
      7 => ['project', 'project'],
      8 => ['gallery', 'gallery'],
      9 => [['distributive', 'webform','landing'], 'landing'],
    ];

    foreach ($content_types as $content_type) {
      if (is_array($content_type[0])) {
        $content_type[0] = implode("','", $content_type[0]);
      }

      // Inicialice.
      foreach ($languages as $lang) {
        $results['content_type'][$content_type[0]]['D7']['original'][$lang] = 0;
        $results['content_type'][$content_type[0]]['D8']['original'][$lang] = 0;
        $results['content_type'][$content_type[0]]['D7']['traduccion'][$lang] = 0;
        $results['content_type'][$content_type[0]]['D8']['traduccion'][$lang] = 0;
      }

      // D7.
      $database = 'webree_d7';
      $sql_original = "
        SELECT COUNT(*) AS num, language
        FROM $database.node
        WHERE TYPE IN ('$content_type[0]')
        AND (nid=tnid OR tnid=0)
        GROUP BY language";
      $res = $this->database->query($sql_original)->fetchAll();
      if ($res) {
        foreach ($res as $val) {
          $results['content_type'][$content_type[0]]['D7']['original'][$val->language] = $val->num;
        }
      }
      $sql_traduccion = "
        SELECT COUNT(*) AS num, language
        FROM $database.node
        WHERE TYPE IN ('$content_type[0]')
        AND (nid!=tnid AND tnid!=0)
        GROUP BY language";
      $res = $this->database->query($sql_traduccion)->fetchAll();
      if ($res) {
        foreach ($res as $val) {
          $results['content_type'][$content_type[0]]['D7']['traduccion'][$val->language] = $val->num;
        }
      }

      // D8.
      $sql_original = "SELECT COUNT(*) AS num, langcode as language FROM node WHERE TYPE='" . $content_type[1] . "' GROUP BY langcode";
      $res = $this->database->query($sql_original)->fetchAll();
      if ($res) {
        foreach ($res as $val) {
          $results['content_type'][$content_type[0]]['D8']['original'][$val->language] = $val->num;
        }
      }
      $sql_traduccion = "SELECT COUNT(*) AS num, langcode as language FROM node_field_data WHERE TYPE='" . $content_type[1] . "' GROUP BY langcode";
      $res = $this->database->query($sql_traduccion)->fetchAll();
      if ($res) {
        foreach ($res as $val) {
          $results['content_type'][$content_type[0]]['D8']['traduccion'][$val->language] = $val->num;
        }
      }
    }

    // Mostrar results table.
    $table = $this->tableContentTypeResults($results);
    print_r($table); echo "<br><br>";
  }

  /**
   * Pinta la tabla de resultados de taxonomias.
   *
   * @param array $res
   *   Array where we save the info.
   *
   * @return string
   *   String that shows the table
   */
  public function tableTaxonomiesResults($res) {
    $file = '';
    $taxonomias = $res['taxonomy'];
    foreach ($taxonomias as $name => $taxonomy) {
      $file .= '<tr>';
      $file .= '<td class="tg-dvid">' . $name . '</td>';
      foreach ($taxonomy as $drupal => $values) {
        $total[$drupal] = 0;
        foreach ($values as $lang => $val) {
          $total[$drupal] = $total[$drupal] + $val;
          $file .= '<td class="tg-0pky">' . $val . '</td>';
        }
        // Total.
        $file .= '<td class="tg-ncd7">' . $total[$drupal] . '</td>';
      }
      // Resultado.
      if ($total['D7'] == $total['D8']) {
        $file .= '<td class="tg-0slm">OK</td>';
      }
      else {
        $file .= '<td class="tg-dwmg">ERROR</td>';
      }
      $file .= '</tr>';
    }

    $table = '
    <style type="text/css">
    .tg  {border-collapse:collapse;border-spacing:0;}
    .tg td{font-family:Arial, sans-serif;font-size:14px;padding:10px 5px;border-style:solid;border-width:1px;overflow:hidden;word-break:normal;border-color:black;}
    .tg th{font-family:Arial, sans-serif;font-size:14px;font-weight:normal;padding:10px 5px;border-style:solid;border-width:1px;overflow:hidden;word-break:normal;border-color:black;}
    .tg .tg-cjtp{background-color:#ecf4ff;border-color:inherit;text-align:left;vertical-align:top}
    .tg .tg-opee{font-weight:bold;background-color:#ffffc7;border-color:inherit;text-align:left;vertical-align:top}
    .tg .tg-dwmg{font-weight:bold;color:#fe0000;border-color:#000000;text-align:left;vertical-align:top}
    .tg .tg-jokb{color:#009901;border-color:#000000;text-align:left;vertical-align:top}
    .tg .tg-5w3z{background-color:#ecf4ff;border-color:inherit;text-align:center;vertical-align:top}
    .tg .tg-0slm{font-weight:bold;color:#009901;border-color:#000000;text-align:left;vertical-align:top}
    .tg .tg-2dfk{font-weight:bold;background-color:#ecf4ff;border-color:inherit;text-align:center;vertical-align:top}
    .tg .tg-dvid{font-weight:bold;background-color:#efefef;border-color:inherit;text-align:left;vertical-align:top}
    .tg .tg-cgxt{font-weight:bold;background-color:#68cbd0;border-color:inherit;text-align:center;vertical-align:middle}
    .tg .tg-fymr{font-weight:bold;border-color:inherit;text-align:left;vertical-align:top}
    .tg .tg-0pky{border-color:inherit;text-align:left;vertical-align:top}
    .tg .tg-ncd7{background-color:#ffffc7;border-color:inherit;text-align:left;vertical-align:top}
    </style>
    <table class="tg" style="undefined;table-layout: fixed; width: 586px">
      <colgroup>
      <col style="width: 112px">
      <col style="width: 63px">
      <col style="width: 63px">
      <col style="width: 63px">
      <col style="width: 63px">
      <col style="width: 63px">
      <col style="width: 63px">
      <col style="width: 63px">
      <col style="width: 63px">
      <col style="width: 125px">
      </colgroup>
      <tr>
        <th class="tg-cgxt" colspan="10">TAXONOMÍAS</th>
      </tr>
      <tr>
        <td class="tg-5w3z" rowspan="2"></td>
        <td class="tg-2dfk" colspan="4">D7</td>
        <td class="tg-2dfk" colspan="4">D8</td>
        <td class="tg-fymr" rowspan="2">RESULTADO</td>
      </tr>
      <tr>
        <td class="tg-cjtp">UND</td>
        <td class="tg-cjtp">ES</td>
        <td class="tg-cjtp">EN</td>
        <td class="tg-opee">TOTAL</td>
        <td class="tg-cjtp">UND</td>
        <td class="tg-cjtp">ES</td>
        <td class="tg-cjtp">EN</td>
        <td class="tg-opee">TOTAL</td>
      </tr>' . $file . '
    </table>';

    return $table;
  }

  /**
   * Pinta la tabla de resultados de taxonomias.
   *
   * @param array $res
   *   Array where we save the info.
   *
   * @return string
   *   String that shows the table
   */
  public function tableContentTypeResults($res) {
    $file = '';
    $content_types = $res['content_type'];
    foreach ($content_types as $name => $content_type) {
      $file .= '<tr>';
      $file .= '<td class="tg-dvid">' . $name . '</td>';
      foreach ($content_type as $drupal => $values) {
        foreach ($values as $status => $vals) {
          $total[$drupal][$status] = 0;
          foreach ($vals as $lang => $val) {
            $total[$drupal][$status] = $total[$drupal][$status] + $val;
            $file .= '<td class="tg-0pky">' . $val . '</td>';
          }
          $class = ($status == 'original') ? 'tg-pidv' : 'tg-ncd7';
          // Total.
          $file .= '<td class="' . $class . '">' . $total[$drupal][$status] . '</td>';
          // Suma.
          if (($drupal == 'D7') && ($status == 'traduccion')) {
            $suma[$drupal] = $total[$drupal]['original'] + $total[$drupal]['traduccion'];
            $txt_suma = $total[$drupal]['original'] . " + " . $total[$drupal]['traduccion'] . " = " . $suma[$drupal];
            $file .= '<td class="' . $class . '">' . $txt_suma . '</td>';
          }
        }
      }
      // Resultado.
      if ($total['D7']['original'] == $total['D8']['original']) {
        $file .= '<td class="tg-0slm">OK</td>';
      }
      else {
        $dif = $total['D7']['original'] - $total['D8']['original'];
        $file .= '<td class="tg-dwmg">ERROR  (' . $dif . ')</td>';
      }
      if ($suma['D7'] == $total['D8']['traduccion']) {
        $file .= '<td class="tg-0slm">OK</td>';
      }
      else {
        $dif = $suma['D7'] - $total['D8']['traduccion'];
        $file .= '<td class="tg-dwmg">ERROR (' . $dif . ')</td>';
      }

      $file .= '</tr>';
    }

    $table = '
      <style type="text/css">
      .tg  {border-collapse:collapse;border-spacing:0;}
      .tg td{font-family:Arial, sans-serif;font-size:14px;padding:10px 5px;border-style:solid;border-width:1px;overflow:hidden;word-break:normal;border-color:black;}
      .tg th{font-family:Arial, sans-serif;font-size:14px;font-weight:normal;padding:10px 5px;border-style:solid;border-width:1px;overflow:hidden;word-break:normal;border-color:black;}
      .tg .tg-cjtp{background-color:#ecf4ff;border-color:inherit;text-align:left;vertical-align:top}
      .tg .tg-opee{font-weight:bold;background-color:#ffffc7;border-color:inherit;text-align:left;vertical-align:top}
      .tg .tg-v0hj{font-weight:bold;background-color:#efefef;border-color:inherit;text-align:center;vertical-align:top}
      .tg .tg-fglx{font-weight:bold;background-color:#ecf4ff;border-color:#343434;text-align:center;vertical-align:top}
      .tg .tg-5w3z{background-color:#ecf4ff;border-color:inherit;text-align:center;vertical-align:top}
      .tg .tg-2dfk{font-weight:bold;background-color:#ecf4ff;border-color:inherit;text-align:center;vertical-align:top}
      .tg .tg-dvid{font-weight:bold;background-color:#efefef;border-color:inherit;text-align:left;vertical-align:top}
      .tg .tg-pidv{background-color:#ffce93;border-color:inherit;text-align:left;vertical-align:top}
      .tg .tg-cgxt{font-weight:bold;background-color:#68cbd0;border-color:inherit;text-align:center;vertical-align:middle}
      .tg .tg-hhqm{background-color:#ecf4ff;border-color:#343434;text-align:left;vertical-align:top}
      .tg .tg-ncd7{background-color:#ffffc7;border-color:inherit;text-align:left;vertical-align:top}
      .tg .tg-y698{background-color:#efefef;border-color:inherit;text-align:left;vertical-align:top}
      .tg .tg-ur59{border-color:#343434;text-align:left;vertical-align:top}
      .tg .tg-0pky{border-color:inherit;text-align:left;vertical-align:top}
      </style>
      <table class="tg" style="undefined;table-layout: fixed; width: 777px">
      <colgroup>
        <col style="width: 112px">
        <col style="width: 63px">
        <col style="width: 63px">
        <col style="width: 63px">
        <col style="width: 63px">
        <col style="width: 63px">
        <col style="width: 63px">
        <col style="width: 63px">
        <col style="width: 63px">
        <col style="width: 140px">
        <col style="width: 63px">
        <col style="width: 63px">
        <col style="width: 63px">
        <col style="width: 63px">
        <col style="width: 63px">
        <col style="width: 63px">
        <col style="width: 63px">
        <col style="width: 125px">
        <col style="width: 125px">
        <col style="width: 125px">
      </colgroup>
        <tr>
    <th class="tg-cgxt" colspan="20">CONTENT TYPES</th>
    </tr>
    <tr>
      <td class="tg-5w3z" rowspan="3"></td>
      <td class="tg-2dfk" colspan="9">D7</td>
      <td class="tg-2dfk" colspan="8">D8</td>
      <td class="tg-v0hj" colspan="2" rowspan="2">RESULTADO</td>
    </tr>
    <tr>
      <td class="tg-2dfk" colspan="4">ORIGINAL</td>
      <td class="tg-2dfk" colspan="5">TRADUCCIÓN</td>
      <td class="tg-2dfk" colspan="4">ORIGINAL</td>
      <td class="tg-2dfk" colspan="4">TRADUCCIÓN</td>
    </tr>
    <tr>
      <td class="tg-cjtp">UND</td>
      <td class="tg-cjtp">ES</td>
      <td class="tg-cjtp">EN</td>
      <td class="tg-pidv"><span style="font-weight:bold">TOTAL</span></td>
      <td class="tg-cjtp">UND</td>
      <td class="tg-cjtp">ES</td>
      <td class="tg-cjtp">EN</td>
      <td class="tg-ncd7"><span style="font-weight:bold">TOTAL</span></td>
      <td class="tg-ncd7"><span style="font-weight:bold">SUMA</span></td>
      <td class="tg-cjtp">UND</td>
      <td class="tg-cjtp">ES</td>
      <td class="tg-cjtp">EN</td>
      <td class="tg-pidv"><span style="font-weight:bold">TOTAL</span></td>
      <td class="tg-cjtp">UND</td>
      <td class="tg-cjtp">ES</td>
      <td class="tg-cjtp">EN</td>
      <td class="tg-ncd7"><span style="font-weight:bold">TOTAL</span></td>
      <td class="tg-ncd7"><span style="font-weight:bold">ORIGINAL</span></td>
      <td class="tg-ncd7"><span style="font-weight:bold">TRADUCCION</span></td>
    </tr>' . $file . '
    </table>';

    return $table;
  }

}
