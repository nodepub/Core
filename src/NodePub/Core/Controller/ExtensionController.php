<?php

namespace NodePub\Core\Controller;

use NodePub\Core\Extension\Extension;
use NodePub\Core\Extension\ExtensionContainer;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * REST Actions for extensions
 */
class ExtensionController
{
    protected $app;
    protected $extensionContainer;
    
    function __construct(Application $app, ExtensionContainer $extensionContainer)
    {
        $this->app = $app;
        $this->extensionContainer = $extensionContainer;
    }

    public function getExtensionsAction()
    {
        return new Response(
            $this->app['twig']->render('@np-admin/panels/extensions.twig', array(
                'extensions' => $this->extensionContainer->getAll(),
                'toolbar' => $this->app['np.admin.toolbar']->getActiveItems($this->app['np.admin.toolbar.group_size']),
            ))
        );
    }

    public function getExtensionAction(Extension $extension)
    {
        return new Response($this->app['twig']->render('@np-admin/panels/extension.twig', array(
            'extension' => $extension,
            'form' => $this->getForm($extension)->createView()
        )));
    }

    public function configureExtensionAction(Extension $extension)
    {
        return new Response($this->app['twig']->render('@np-admin/panels/extension_edit.twig', array(
            'extension' => $extension,
            'form' => $this->getForm($post)->createView()
        )));
    }

    public function updateExtensionAction(Request $request, Extension $extension)
    {
        $form = $this->getForm($post)->bindRequest($request);
    
        if ($form->isValid()) {
            //$post = $this->postManager->savePost($post);
    
            return $this->app->redirect($this->app['url_generator']->generate('admin_dashboard'));
        } else {
            # forward to template for errors
        }
    }
    
    public function installExtensionAction(Extension $extension)
    {
    }

    public function uninstallExtensionAction(Extension $extension)
    {
    }

    public function activateExtensionAction(Extension $extension)
    {
    }

    public function deactivateExtensionAction(Extension $extension)
    {
    }

    protected function getForm(Extension $extension) {
        $form = $this->app['form.factory']
            ->createBuilder(new ExtensionType(), $extension)
            ->getForm();

        return $form;
    }
}