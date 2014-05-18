<?php

namespace AssetManagerTest\Service;

use AssetManager\Service\AssetCacheManager;
use AssetManager\Service\AssetFilterManager;
use PHPUnit_Framework_TestCase;
use Assetic\Asset;
use AssetManager\Cache\FilePathCache;
use AssetManager\Service\AssetManager;
use AssetManager\Service\MimeResolver;
use Zend\Http\Response;
use Zend\Http\PhpEnvironment\Request;
use Zend\Console\Request as ConsoleRequest;

class AssetManagerTest extends PHPUnit_Framework_TestCase
{
    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        require_once __DIR__ . '/../../_files/JSMin.inc';
        require_once __DIR__ . '/../../_files/CustomFilter.php';
        require_once __DIR__ . '/../../_files/BrokenFilter.php';
    }

    protected function getRequest()
    {
        $request = new Request();
        $request->setUri('http://localhost/base-path/asset-path');
        $request->setBasePath('/base-path');

        return $request;
    }

    /**
     * @param string $resolveTo
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|\AssetManager\Resolver\ResolverInterface
     */
    protected function getResolver($resolveTo = __FILE__)
    {
        $mimeResolver    = new MimeResolver;
        $asset           = new Asset\FileAsset($resolveTo);
        $asset->mimetype = $mimeResolver->getMimeType($resolveTo);
        $resolver        = $this->getMock('AssetManager\Resolver\ResolverInterface');
        $resolver
                ->expects($this->once())
                ->method('resolve')
                ->with('asset-path')
                ->will($this->returnValue($asset));

        return $resolver;
    }

    public function testConstruct()
    {
        $resolver     = $this->getMock('AssetManager\Resolver\ResolverInterface');
        $assetManager = new AssetManager($resolver, array('herp', 'derp'));

        $this->assertSame($resolver, $assetManager->getResolver());
        $this->assertAttributeEquals(array('herp', 'derp'), 'config', $assetManager);
    }

    /**
     * @expectedException \PHPUnit_Framework_Error
     */
    public function testConstructFailsOnOtherType()
    {
        new AssetManager('invalid');
    }

    public function testInvalidRequest()
    {
        $mimeResolver    = new MimeResolver;
        $asset           = new Asset\FileAsset(__FILE__);
        $asset->mimetype = $mimeResolver->getMimeType(__FILE__);
        $resolver        = $this->getMock('AssetManager\Resolver\ResolverInterface');
        $resolver
                ->expects($this->any())
                ->method('resolve')
                ->with('asset-path')
                ->will($this->returnValue($asset));

        $request = new ConsoleRequest();

        $assetManager    = new AssetManager($resolver);
        $resolvesToAsset = $assetManager->resolvesToAsset($request);

        $this->assertFalse($resolvesToAsset);
    }

    public function testResolvesToAsset()
    {
        $assetManager    = new AssetManager($this->getResolver());
        $resolvesToAsset = $assetManager->resolvesToAsset($this->getRequest());

        $this->assertTrue($resolvesToAsset);
    }

    /*
     * Mock will throw error if called more than once
     */

    public function testResolvesToAssetCalledOnce()
    {
        $assetManager = new AssetManager($this->getResolver());
        $assetManager->resolvesToAsset($this->getRequest());
        $assetManager->resolvesToAsset($this->getRequest());
    }

    public function testResolvesToAssetReturnsBoolean()
    {
        $assetManager    = new AssetManager($this->getResolver());
        $resolvesToAsset = $assetManager->resolvesToAsset($this->getRequest());

        $this->assertTrue(is_bool($resolvesToAsset));
    }

    /*
     * Test if works by checking if is same reference to instance
     */

    public function testSetResolver()
    {
        $assetManager = new AssetManager($this->getMock('AssetManager\Resolver\ResolverInterface'));

        $newResolver = $this->getMock('AssetManager\Resolver\ResolverInterface');
        $assetManager->setResolver($newResolver);

        $this->assertSame($newResolver, $assetManager->getResolver());
    }

    /**
     * @expectedException \PHPUnit_Framework_Error
     */
    public function testSetResolverFailsOnInvalidType()
    {
        new AssetManager('invalid');
    }

    /*
     * Added for the sake of method coverage.
     */

    public function testGetResolver()
    {
        $resolver     = $this->getMock('AssetManager\Resolver\ResolverInterface');
        $assetManager = new AssetManager($resolver);

        $this->assertSame($resolver, $assetManager->getResolver());
    }

    public function testSetStandardFilters()
    {
        $config = array(
            'filters' => array(
                'asset-path' => array(
                    array(
                        'filter' => 'JSMin',
                    ),
                ),
            ),
        );

        $assetFilterManager = new AssetFilterManager($config['filters']);
        $assetCacheManager  = new AssetCacheManager();

        $response     = new Response;
        $resolver     = $this->getResolver(__DIR__ . '/../../_files/require-jquery.js');
        $request      = $this->getRequest();
        $assetManager = new AssetManager($resolver, $config);
        $minified     = \JSMin::minify(file_get_contents(__DIR__ . '/../../_files/require-jquery.js'));
        $assetManager->setAssetFilterManager($assetFilterManager);
        $assetManager->setAssetCacheManager($assetCacheManager);
        $this->assertTrue($assetManager->resolvesToAsset($request));
        $assetManager->setAssetOnResponse($response);
        $this->assertEquals($minified, $response->getBody());
    }

    public function testSetExtensionFilters()
    {
        $config = array(
            'filters' => array(
                'js' => array(
                    array(
                        'filter' => 'JSMin',
                    ),
                ),
            ),
        );

        $assetFilterManager = new AssetFilterManager($config['filters']);
        $assetCacheManager  = new AssetCacheManager();

        $mimeResolver = new MimeResolver;
        $response     = new Response;
        $resolver     = $this->getResolver(__DIR__ . '/../../_files/require-jquery.js');
        $request      = $this->getRequest();
        $assetManager = new AssetManager($resolver, $config);
        $minified     = \JSMin::minify(file_get_contents(__DIR__ . '/../../_files/require-jquery.js'));
        $assetFilterManager->setMimeResolver($mimeResolver);
        $assetManager->setAssetFilterManager($assetFilterManager);
        $assetManager->setAssetCacheManager($assetCacheManager);

        $this->assertTrue($assetManager->resolvesToAsset($request));
        $assetManager->setAssetOnResponse($response);
        $this->assertEquals($minified, $response->getBody());
    }

    public function testSetMimeTypeFilters()
    {
        $config = array(
            'filters' => array(
                'application/javascript' => array(
                    array(
                        'filter' => 'JSMin',
                    ),
                ),
            ),
        );

        $assetFilterManager = new AssetFilterManager($config['filters']);
        $assetCacheManager  = new AssetCacheManager();
        $mimeResolver       = new MimeResolver;
        $response           = new Response;
        $resolver           = $this->getResolver(__DIR__ . '/../../_files/require-jquery.js');
        $request            = $this->getRequest();
        $assetManager       = new AssetManager($resolver, $config);
        $minified           = \JSMin::minify(file_get_contents(__DIR__ . '/../../_files/require-jquery.js'));
        $assetFilterManager->setMimeResolver($mimeResolver);
        $assetManager->setAssetFilterManager($assetFilterManager);
        $assetManager->setAssetCacheManager($assetCacheManager);

        $this->assertTrue($assetManager->resolvesToAsset($request));
        $assetManager->setAssetOnResponse($response);
        $this->assertEquals($minified, $response->getBody());
    }

    public function testCustomFilters()
    {
        $config = array(
            'filters' => array(
                'asset-path' => array(
                    array(
                        'filter' => new \CustomFilter,
                    ),
                ),
            ),
        );

        $assetFilterManager = new AssetFilterManager($config['filters']);
        $assetCacheManager  = new AssetCacheManager();
        $mimeResolver       = new MimeResolver;
        $response           = new Response;
        $resolver           = $this->getResolver(__DIR__ . '/../../_files/require-jquery.js');
        $request            = $this->getRequest();
        $assetManager       = new AssetManager($resolver, $config);
        $assetFilterManager->setMimeResolver($mimeResolver);
        $assetManager->setAssetFilterManager($assetFilterManager);
        $assetManager->setAssetCacheManager($assetCacheManager);

        $this->assertTrue($assetManager->resolvesToAsset($request));
        $assetManager->setAssetOnResponse($response);
        $this->assertEquals('called', $response->getBody());
    }

    public function testSetEmptyFilters()
    {
        $config = array(
            'filters' => array(
                'asset-path' => array(
                ),
            ),
        );

        $assetFilterManager = new AssetFilterManager($config['filters']);
        $assetCacheManager  = new AssetCacheManager();
        $mimeResolver       = new MimeResolver;
        $response           = new Response;
        $resolver           = $this->getResolver(__DIR__ . '/../../_files/require-jquery.js');
        $request            = $this->getRequest();
        $assetManager       = new AssetManager($resolver, $config);
        $assetFilterManager->setMimeResolver($mimeResolver);
        $assetManager->setAssetFilterManager($assetFilterManager);
        $assetManager->setAssetCacheManager($assetCacheManager);

        $this->assertTrue($assetManager->resolvesToAsset($request));
        $assetManager->setAssetOnResponse($response);
        $this->assertEquals(file_get_contents(__DIR__ . '/../../_files/require-jquery.js'), $response->getBody());
    }

    /**
     * @expectedException \AssetManager\Exception\RuntimeException
     */
    public function testSetFalseClassFilter()
    {
        $config = array(
            'filters' => array(
                'asset-path' => array(
                    array(
                        'filter' => 'Bacon',
                    ),
                ),
            ),
        );

        $assetFilterManager = new AssetFilterManager($config['filters']);
        $assetCacheManager  = new AssetCacheManager();
        $mimeResolver       = new MimeResolver;
        $response           = new Response;
        $resolver           = $this->getResolver(__DIR__ . '/../../_files/require-jquery.js');
        $request            = $this->getRequest();
        $assetManager       = new AssetManager($resolver, $config);
        $assetFilterManager->setMimeResolver($mimeResolver);
        $assetManager->setAssetFilterManager($assetFilterManager);
        $assetManager->setAssetCacheManager($assetCacheManager);
        $assetManager->resolvesToAsset($request);
        $assetManager->setAssetOnResponse($response);
    }

    public function testSetStandardCacheDefault()
    {
        $config = array(
            'caching' => array(
                'default' => array(
                    'cache'   => 'FilePath',
                    'options' => array(
                        'dir' => $this->getTmpDir(),
                    ),
                ),
            ),
        );

        $assetFilterManager = new AssetFilterManager();
        $assetCacheManager  = new AssetCacheManager($config['caching']);
        $mimeResolver       = new MimeResolver;
        $response           = new Response;
        $resolver           = $this->getResolver();
        $request            = $this->getRequest();
        $assetManager       = new AssetManager($resolver, $config);
        $assetFilterManager->setMimeResolver($mimeResolver);
        $assetManager->setAssetFilterManager($assetFilterManager);
        $assetManager->setAssetCacheManager($assetCacheManager);
        $this->assertTrue($assetManager->resolvesToAsset($request));
        $assetManager->setAssetOnResponse($response);
        $this->assertEquals(file_get_contents(__FILE__), $response->getBody());
    }

    public function testSetStandardCacheAssetSpecific()
    {
        $config = array(
            'caching' => array(
                'asset-path' => array(
                    'cache'   => 'FilePath',
                    'options' => array(
                        'dir' => $this->getTmpDir(),
                    ),
                ),
            ),
        );

        $assetFilterManager = new AssetFilterManager();
        $assetCacheManager  = new AssetCacheManager($config['caching']);
        $mimeResolver       = new MimeResolver;
        $response           = new Response;
        $resolver           = $this->getResolver();
        $request            = $this->getRequest();
        $assetManager       = new AssetManager($resolver, $config);
        $assetFilterManager->setMimeResolver($mimeResolver);
        $assetManager->setAssetFilterManager($assetFilterManager);
        $assetManager->setAssetCacheManager($assetCacheManager);
        $this->assertTrue($assetManager->resolvesToAsset($request));
        $assetManager->setAssetOnResponse($response);
        $this->assertEquals(file_get_contents(__FILE__), $response->getBody());
    }

    public function testSetStandardCacheEmpty()
    {
        $config = array(
            'caching' => array(
                'asset-path' => array(
                    'cache' => '',
                ),
            ),
        );

        $assetFilterManager = new AssetFilterManager();
        $assetCacheManager  = new AssetCacheManager($config['caching']);
        $mimeResolver       = new MimeResolver;
        $response           = new Response;
        $resolver           = $this->getResolver();
        $request            = $this->getRequest();
        $assetManager       = new AssetManager($resolver, $config);
        $assetFilterManager->setMimeResolver($mimeResolver);
        $assetManager->setAssetFilterManager($assetFilterManager);
        $assetManager->setAssetCacheManager($assetCacheManager);
        $this->assertTrue($assetManager->resolvesToAsset($request));
        $assetManager->setAssetOnResponse($response);
        $this->assertEquals(file_get_contents(__FILE__), $response->getBody());
    }

    public function testSetDefaultCacheFileSystem()
    {
        $config = array(
            'caching' => array(
                'asset-path' => array(
                    'cache'   => 'FileSystem',
                    'options' => array(
                        'dir' => $this->getTmpDir(),
                    ),
                ),
            ),
        );

        $assetFilterManager = new AssetFilterManager();
        $assetCacheManager  = new AssetCacheManager($config['caching']);
        $mimeResolver       = new MimeResolver;
        $response           = new Response;
        $resolver           = $this->getResolver();
        $request            = $this->getRequest();
        $assetManager       = new AssetManager($resolver, $config);
        $assetFilterManager->setMimeResolver($mimeResolver);
        $assetManager->setAssetFilterManager($assetFilterManager);
        $assetManager->setAssetCacheManager($assetCacheManager);
        $this->assertTrue($assetManager->resolvesToAsset($request));
        $assetManager->setAssetOnResponse($response);
        $this->assertEquals(file_get_contents(__FILE__), $response->getBody());
    }

    public function testSetCallbackCache()
    {
        $test   = $this;
        $config = array(
            'caching' => array(
                'asset-path' => array(
                    'cache' => function ($file) use ($test) {
                        return new FilePathCache($test->getTmpDir(), $file);
                    },
                ),
            ),
        );

        $assetFilterManager = new AssetFilterManager();
        $assetCacheManager  = new AssetCacheManager($config['caching']);
        $mimeResolver       = new MimeResolver;
        $response           = new Response;
        $resolver           = $this->getResolver();
        $request            = $this->getRequest();
        $assetManager       = new AssetManager($resolver, $config);
        $assetFilterManager->setMimeResolver($mimeResolver);
        $assetManager->setAssetFilterManager($assetFilterManager);
        $assetManager->setAssetCacheManager($assetCacheManager);
        $this->assertTrue($assetManager->resolvesToAsset($request));
        $assetManager->setAssetOnResponse($response);
        $this->assertEquals(file_get_contents(__FILE__), $response->getBody());
    }

    public function testSetCallbackCacheInvalid()
    {
        $config = array(
            'caching' => array(
                'asset-path' => array(
                    'cache' => function () {
                        return new \stdClass;
                    },
                ),
            ),
        );

        $assetFilterManager = new AssetFilterManager();
        $assetCacheManager  = new AssetCacheManager($config['caching']);
        $mimeResolver       = new MimeResolver;
        $response           = new Response;
        $resolver           = $this->getResolver();
        $request            = $this->getRequest();
        $assetManager       = new AssetManager($resolver, $config);
        $assetFilterManager->setMimeResolver($mimeResolver);
        $assetManager->setAssetFilterManager($assetFilterManager);
        $assetManager->setAssetCacheManager($assetCacheManager);
        $assetManager->resolvesToAsset($request);
        $assetManager->setAssetOnResponse($response);
    }

    public function testSetAssetOnResponse()
    {

        $assetFilterManager = new AssetFilterManager();
        $assetCacheManager  = new AssetCacheManager();
        $mimeResolver       = new MimeResolver;
        $assetManager       = new AssetManager($this->getResolver());
        $assetFilterManager->setMimeResolver($mimeResolver);
        $assetManager->setAssetFilterManager($assetFilterManager);
        $assetManager->setAssetCacheManager($assetCacheManager);
        $request            = $this->getRequest();
        $assetManager->resolvesToAsset($request);
        $response           = $assetManager->setAssetOnResponse(new Response);

        $this->assertSame(file_get_contents(__FILE__), $response->getContent());
    }

    public function testAssetSetOnResponse()
    {
        $assetManager = new AssetManager($this->getResolver());
        $this->assertFalse($assetManager->assetSetOnResponse());

        $assetFilterManager = new \AssetManager\Service\AssetFilterManager();
        $assetFilterManager->setMimeResolver(new MimeResolver);
        $assetManager->setAssetFilterManager($assetFilterManager);
        $assetManager->setAssetCacheManager(new \AssetManager\Service\AssetCacheManager());
        $assetManager->resolvesToAsset($this->getRequest());
        $assetManager->setAssetOnResponse(new Response);

        $this->assertTrue($assetManager->assetSetOnResponse());
    }

    /**
     * @expectedException \AssetManager\Exception\RuntimeException
     */
    public function testSetAssetOnResponseNoMimeType()
    {
        $asset    = new Asset\FileAsset(__FILE__);
        $resolver = $this->getMock('AssetManager\Resolver\ResolverInterface');
        $resolver
                ->expects($this->once())
                ->method('resolve')
                ->with('asset-path')
                ->will($this->returnValue($asset));

        $assetManager = new AssetManager($resolver);
        $request      = $this->getRequest();
        $assetManager->resolvesToAsset($request);

        $assetManager->setAssetOnResponse(new Response);
    }

    public function testResponseHeadersForAsset()
    {
        $mimeResolver       = new MimeResolver;
        $assetFilterManager = new AssetFilterManager();
        $assetCacheManager  = new AssetCacheManager();
        $assetManager       = new AssetManager($this->getResolver());
        $assetFilterManager->setMimeResolver($mimeResolver);
        $assetManager->setAssetFilterManager($assetFilterManager);
        $assetManager->setAssetCacheManager($assetCacheManager);

        $request  = $this->getRequest();
        $assetManager->resolvesToAsset($request);
        $response = $assetManager->setAssetOnResponse(new Response);
        $thisFile = file_get_contents(__FILE__);

        if (function_exists('mb_strlen')) {
            $fileSize = mb_strlen($thisFile, '8bit');
        } else {
            $fileSize = strlen($thisFile);
        }

        $mimeType = $mimeResolver->getMimeType(__FILE__);

        $headers = 'Content-Transfer-Encoding: binary' . "\r\n";
        $headers .= 'Content-Type: ' . $mimeType . "\r\n";
        $headers .= 'Content-Length: ' . $fileSize . "\r\n";
        $this->assertSame($headers, $response->getHeaders()->toString());
    }

    /**
     * @expectedException \AssetManager\Exception\RuntimeException
     */
    public function testSetAssetOnReponseFailsWhenNotResolved()
    {
        $resolver     = $this->getMock('AssetManager\Resolver\ResolverInterface');
        $assetManager = new AssetManager($resolver);

        $assetManager->setAssetOnResponse(new Response);
    }

    public function testResolvesToAssetNotFound()
    {
        $resolver        = $this->getMock('AssetManager\Resolver\ResolverInterface');
        $assetManager    = new AssetManager($resolver);
        $resolvesToAsset = $assetManager->resolvesToAsset(new Request);

        $this->assertFalse($resolvesToAsset);
    }

    /**
     * @return string
     */
    public function getTmpDir()
    {
        $tmp = sys_get_temp_dir() . '/' . uniqid('am');

        mkdir($tmp);

        return $tmp;
    }
}
