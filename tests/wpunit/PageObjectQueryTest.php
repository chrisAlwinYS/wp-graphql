<?php 
class PageObjectQueryTest extends \Codeception\TestCase\WPTestCase
{
    public $page;
    public $user;

    public function setUp(): void
    {

        WPGraphQL::clear_schema();

        $this->set_permalink_structure('/%year%/%monthnum%/%day%/%postname%/');

        $this->user = $this->factory()->user->create([
            'role' => 'administrator',
        ]);

        $this->page = $this->factory()->post->create([
            'post_type' => 'page',
            'post_status' => 'publish',
            'post_title' => 'Test Page',
            'post_author' => $this->user
        ]);


        parent::setUp();

    }

    public function tearDown(): void {

        WPGraphQL::clear_schema();
        $this->set_permalink_structure( '/%year%/%monthnum%/%day%/%postname%/' );
        parent::tearDown();
        wp_delete_post( $this->page );
        wp_delete_user( $this->user );

    }

    function testPageReturnsTitleByURI () {
        $query = '
        query Get_Page_Title ($uri: ID!) {
            page(id: $uri, idType: URI){
                title
            }
        }
        ';

        $actual = graphql([
            'query' => $query,
            'variables' => [
                'uri' => get_permalink( $this->page ),
            ],
        ]);

        codecept_debug( get_permalink( $this->page ) );
        codecept_debug( $actual );


        $this->assertArrayNotHasKey( 'errors', $actual );

        $this->assertSame( get_the_title($this->page), $actual['data']['page']['title'] );
    }

    function testPageReturnsTemplateByURI () {
        $query = '
        query Get_Page_Title ($uri: ID!) {
              page(id: $uri, idType: URI) {
                title
                template {
                  templateFile
                  templateName
                }
              }
            }
        ';

        $actual = graphql([
            'query' => $query,
            'variables' => [
                'uri' => get_permalink( $this->page ),
            ],
        ]);

        codecept_debug( get_permalink( $this->page ) );
        codecept_debug( $actual );

        $this->assertArrayNotHasKey( 'errors', $actual );

        $this->assertSame( get_the_title($this->page), $actual['data']['page']['title'] );
        $this->assertSame( 'Default', $actual['data']['page']['template']['templateName']);

    }


    function testPageReturnsCustomTemplateByURI () {
        update_post_meta( $this->page, '_wp_page_template', "dynamic.php" );

        $query = '
        query Get_Page_Title ($uri: ID!) {
              page(id: $uri, idType: URI) {
                title
                template {
                  templateFile
                  templateName
                }
              }
            }
        ';

        $actual = graphql([
            'query' => $query,
            'variables' => [
                'uri' => get_permalink( $this->page ),
            ],
        ]);

        codecept_debug( get_permalink( $this->page ) );
        codecept_debug( $actual );

        $this->assertArrayNotHasKey( 'errors', $actual);
        $this->assertSame( get_the_title($this->page), $actual['data']['page']['title']);
        $this->assertSame( 'dynamic.php', $actual['data']['page']['template']['templateName']);

    }



}