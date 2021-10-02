<?php

namespace Drupal\Tests\freelinking\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\file\Entity\File;

/**
 * Test that freelinking filter is functional.
 *
 * @group freelinking
 */
class FreelinkingFilterTest extends BrowserTestBase {

  static public $modules = ['node', 'user', 'file', 'filter', 'search', 'freelinking'];

  /**
   * A privileged user account to test with.
   *
   * @var \Drupal\user\Entity\User
   */
  protected $privilegedUser;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->privilegedUser = $this->createUser([
      'bypass node access',
      'administer filters',
      'access user profiles',
    ]);
    $this->drupalLogin($this->privilegedUser);

    // Create a content type.
    $this->createContentType(['name' => 'Basic page', 'type' => 'page']);

    /** @var \Drupal\Core\File\FileSystemInterface $filesystem */
    $filesystem = $this->container->get('file_system');

    // Make sure that freelinking filter is activated.
    $edit = [
      'filters[freelinking][status]' => 1,
      'filters[freelinking][weight]' => 0,
      'filters[freelinking][settings][plugins][nodetitle][enabled]' => 1,
      'filters[freelinking][settings][plugins][external][enabled]' => 1,
      'filters[freelinking][settings][plugins][external][settings][scrape]' => 0,
      'filters[freelinking][settings][plugins][file][enabled]' => 1,
      'filters[freelinking][settings][plugins][file][settings][scheme]' => 'public',
      'filters[freelinking][settings][plugins][drupalorg][enabled]' => 1,
      'filters[freelinking][settings][plugins][drupalorg][settings][scrape]' => 0,
      'filters[freelinking][settings][plugins][drupalorg][settings][node]' => 1,
      'filters[freelinking][settings][plugins][drupalorg][settings][project]' => 1,
      'filters[freelinking][settings][plugins][google][enabled]' => 1,
      'filters[freelinking][settings][plugins][nid][enabled]' => 1,
      'filters[freelinking][settings][plugins][path_alias][enabled]' => 1,
      'filters[freelinking][settings][plugins][search][enabled]' => 1,
      'filters[freelinking][settings][plugins][user][enabled]' => 1,
      'filters[freelinking][settings][plugins][wiki][enabled]' => 1,
      'filters[filter_url][weight]' => 1,
      'filters[filter_html][weight]' => 2,
      'filters[filter_autop][weight]' => 3,
      'filters[filter_htmlcorrector][weight]' => 4,
    ];
    $this->drupalPostForm('/admin/config/content/formats/manage/plain_text', $edit, t('Save configuration'));
    $this->assertText(t('The text format Plain text has been updated.'));
    $this->drupalGet('admin/config/content/formats/manage/plain_text');
    $this->assertFieldChecked('edit-filters-freelinking-status');

    // Create a couple of pages which will be freelinked.
    $edit = [];
    $edit['title[0][value]'] = t('First page');
    $edit['body[0][value]'] = t('Body of first page');
    $this->drupalPostForm('/node/add/page', $edit, t('Save'));
    $this->assertText(t('Basic page @title has been created.', ['@title' => $edit['title[0][value]']]));

    $edit = [];
    $edit['title[0][value]'] = t('Second page');
    $edit['body[0][value]'] = t('Body of second page');
    $this->drupalPostForm('/node/add/page', $edit, t('Save'));
    $this->assertText(t('Basic page @title has been created.', ['@title' => $edit['title[0][value]']]));

    $edit = [];
    $edit['title[0][value]'] = t('Third page');
    $edit['body[0][value]'] = t('Body of third page');
    $this->drupalPostForm('/node/add/page', $edit, t('Save'));
    $this->assertText(t('Basic page @title has been created.', ['@title' => $edit['title[0][value]']]));

    // Upload Drupal logo to files directory to test file and image plugins.
    $root_path = $_SERVER['DOCUMENT_ROOT'];
    $image_path = $root_path . '/core/themes/bartik/logo.png';
    file_unmanaged_copy($image_path, 'public://logo.png');
    $image = File::create([
      'uri' => 'public://logo.png',
      'status' => FILE_STATUS_PERMANENT,
    ]);
    $image->save();
    $this->assertTrue(is_string($filesystem->realpath('public://logo.png')),
                      t('Image @image was saved successfully',
                      ['@image' => 'public://logo.png']));
  }

  /**
   * Tests all plugins
   */
  public function testFreelinkingPlugins() {
    // Create node that will contain a sample of each plugin.
    $edit = [];
    $edit['title[0][value]'] = t('Testing all freelinking plugins');
    $edit['body[0][value]'] = '<ul>' .
                    '  <li>Default plugin (nodetitle):  [[First page]]</li>' .
                    '  <li>Nodetitle:      [[nodetitle:Second page]]</li>' .
                    '  <li>Nid:            [[nid:2]]</li>' .
                    '  <li>User:           [[u:' . $this->privilegedUser->id() . ']]</li>' .
                    '  <li>Drupalproject:  [[drupalproject:freelinking]]</li>' .
                    '  <li>Drupalorg:      [[drupalorg:1]]</li>' .
                    '  <li>Search:         [[search:test]]</li>' .
                    '  <li>Google:         [[google:drupal]]</li>' .
                    '  <li>File:           [[file:logo.png]]</li>' .
                    '  <li>Wikipedia:      [[wikipedia:Main_Page]]</li>' .
                    '  <li>Wikiquote:      [[wikiquote:Main Page]]</li>' .
                    '  <li>Wiktionary:     [[wiktionary:Main Page]]</li>' .
                    '  <li>Wikinews:       [[wikinews:Main Page]]</li>' .
                    '  <li>Wikisource:     [[wikisource:Main Page]]</li>' .
                    '  <li>Wikibooks:      [[wikibooks:Main Page]]</li>' .
                    '  <li>Showtext:       [[showtext:Shown Text]]</li>' .
                    '  <li>Nowiki:         [[nowiki:No Wiki]]</li>' .
                    '</ul>' .
                    '<p>Testing compatibility with other modules</p>' .
                    '<ul>' .
                    '  <li>Respects [[drupalproject:media]] tags, such as:' .
                    '  [[{"type":"media","view_mode":"media_large","fid":"286","attributes":{"alt":"","class":"media-image","typeof":"foaf:Image"}}]]</li>' .
                    '</ul>';

    $this->drupalPostForm('node/add/page', $edit, t('Save'));
    $this->assertText(t('Basic page @title has been created.', ['@title' => $edit['title[0][value]']]));

    // Verify each freelink plugin.
    $this->assertLink(t('First page'), 0, t('Generate default plugin (nodetitle) freelink.'));
    $this->assertLink(t('Second page'), 0, t('Generate Nodetitle freelink.'));
    $this->assertLink(t('Second page'), 0, t('Generate Nid freelink.'));
    $this->assertLink($this->privilegedUser->getAccountName(), 0, t('Generate User freelink.'));
    $this->assertLinkByHref('https://drupal.org/project/freelinking', 0, t('Generate Drupalproject freelink.'));
    $this->assertLinkByHref('https://drupal.org/node/1', 0, t('Generate Drupalorg freelink.'));
    $this->assertLinkByHref('/search/node?keys=test', 0, t('Generate Search freelink.'));
    $this->assertLinkByHref('https://google.com/search?q=drupal&hl=en', 0, t('Generate Google freelink.'));
    $this->assertLink('logo.png', 0, t('Generate File freelink.'));
    $this->assertLinkByHref('https://en.wikipedia.org/wiki/Main_Page', 0, t('Generate Wikipedia freelink.'));
    $this->assertLinkByHref('https://en.wikisource.org/wiki/Main_Page', 0, t('Generate Wikisource freelink.'));
    $this->assertLinkByHref('https://en.wiktionary.org/wiki/Main_Page', 0, t('Generate Wiktionary freelink.'));
    $this->assertLinkByHref('https://en.wikiquote.org/wiki/Main_Page', 0, t('Generate Wikiquote freelink.'));
    $this->assertLinkByHref('https://en.wikibooks.org/wiki/Main_Page', 0, t('Generate Wikibooks freelink.'));
    $this->assertLinkByHref('https://en.wikinews.org/wiki/Main_Page', 0, t('Generate Wikinews freelink.'));
    $this->assertText('Shown Text');
    $this->assertText('[[No Wiki]]');
    $this->pass(t('Verifying compatibility with other modules...'));

    // @todo Media module parse test.
  }

}
