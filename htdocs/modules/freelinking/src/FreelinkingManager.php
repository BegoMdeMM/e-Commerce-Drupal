<?php

namespace Drupal\freelinking;

use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\freelinking\Plugin\FreelinkingPluginInterface;

/**
 * Freelinking plugin manager.
 */
class FreelinkingManager extends DefaultPluginManager implements FreelinkingManagerInterface {

  use StringTranslationTrait;

  /**
   * Language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler, LanguageManagerInterface $language_manager) {
    parent::__construct('Plugin/freelinking', $namespaces, $module_handler, '\Drupal\freelinking\Plugin\FreelinkingPluginInterface', '\Drupal\freelinking\Annotation\Freelinking');

    $this->alterInfo('freelinking_plugin_info');
    $this->setCacheBackend($cache_backend, 'freelinking');
    $this->languageManager = $language_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginFromIndicator($indicator, $allowed_plugins = array(), $options = array()) {
    $current_plugin = FALSE;
    $default_configuration = [
      'settings' => [],
    ];

    try {
      if (('showtext' === $indicator) || ('nowiki' === $indicator) || ('redact' === $indicator)) {
        return $this->createInstance('builtin', []);
      }

      foreach ($allowed_plugins as $plugin_name => $plugin_info) {
        /** @var \Drupal\freelinking\Plugin\FreelinkingPluginInterface $plugin */
        $default_configuration['settings'] = isset($plugin_info['settings']) ? $plugin_info['settings'] : [];
        $plugin = $this->createInstance($plugin_name, $default_configuration);
        if (preg_match($plugin->getIndicator(), $indicator)) {
          $current_plugin = $plugin;
        }
      }

      // Set the current plugin to nodetitle if it is the default.
      if (!$options['global_options']['ignore_upi'] &&
          in_array('nodetitle', $allowed_plugins) &&
          'nodetitle' === $options['default'] && !$current_plugin) {
        $default_configuration['settings'] = $allowed_plugins['nodetitle']['settings'];
        $current_plugin = $this->createInstance('nodetitle', $default_configuration);
      }

      return $current_plugin;
    }
    catch (PluginNotFoundException $e) {
      return $current_plugin;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function buildLink(FreelinkingPluginInterface $plugin, array $target) {
    $link = $plugin->buildLink($target);

    // Allow modules a chance to alter the freelink link array for complex
    // plugins that return an array.
    if (is_array($link) && !isset($link['error'])) {
      $data = [
        'target' => $target,
        'plugin_name' => $plugin->getPluginId(),
        'plugin' => $plugin,
      ];

      $this->moduleHandler->alter('freelinking_freelink', $link, $data);
    }

    return $link;
  }

  /**
   * {@inheritdoc}
   */
  public function parseTarget($target, $langcode) {
    $args = [];
    $args['target'] = $target;
    $args['other'] = [];
    $items = explode('|', $target);

    // The first three unnamed arguments are dest, text, and tooltip.
    $index = 0;
    foreach ($items as $key => $item) {
      // Set argument with INI-style configuration.
      if (strpos($item, '=')) {
        list($name, $value) = explode('=', $item);
        $args[$name] = $value;
      }
      elseif ($index < 3) {
        switch ($index) {
          case 0:
            $args['dest'] = $item;
            break;

          case 1:
            $args['text'] = $item;
            break;

          case 2:
            $args['tooltip'] = $item;
            break;
        }
        $index++;
      }
      else {
        $args['other'][] = $item;
      }
    }

    // Convert URL-encoded text into something readable for link text & tooltip.
    $args['text'] = isset($args['text']) ? urldecode($args['text']) : NULL;
    $args['tooltip'] = isset($args['tooltip']) ? urldecode($args['tooltip']) : NULL;
    $args['language'] = $this->languageManager->getLanguage($langcode);

    return $args;
  }

  /**
   * {@inheritdoc}
   */
  public function createErrorElement($indicator) {
    $message = $this->t('Missing plugin indicator');

    if ('NONE' !== $indicator) {
      $message = $this->t('Unknown plugin indicator');
    }

    return [
      '#theme' => 'freelink_error',
      '#message' => $message,
      '#plugin' => $indicator,
      '#attributes' => [],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function createFreelinkElement($plugin_id, $target, $indicator, $langcode, $plugin_settings_string) {
    $plugin_settings = unserialize($plugin_settings_string);
    $configuration = [
      'settings' => isset($plugin_settings['settings']) ? $plugin_settings['settings'] : [],
    ];

    $plugin = $this->createInstance($plugin_id, $configuration);
    $target_array = $this->parseTarget($target, $langcode);
    $target_array['indicator'] = $indicator;
    $link = $this->buildLink($plugin, $target_array);

    // Drupal currently does not allow returning a render element that only does
    // #pre_render as part of a lazy builder context. This wraps the render
    // array within a small theme function.
    return [
      '#theme' => 'freelink',
      '#link' => $link,
    ];
  }

}
