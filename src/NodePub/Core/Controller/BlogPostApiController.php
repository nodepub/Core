<?php

namespace NodePub\Core\Controller;

use NodePub\BlogEngine\Post;
use NodePub\BlogBundle\Form\Type\PostType;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * TODO: This is in the middle of a refactor
 */
class BlogPostApiController
{
    const ERR_MSG_404 = 'No posts were found with the given parameters.';

    protected $app;
    protected $postManager;
    protected $responseHelper;
    
    function __construct(Application $app, $postManager, $responseHelper)
    {
        $this->app = $app;
        $this->postManager = $postManager;
        $this->responseHelper = $responseHelper;
    }

    /**
     * @Route("/posts", name="npb_api_get_posts")
     * @Method("GET")
     */
    public function getPostsAction()
    {
        return $this->responseHelper->prepareJsonResponse(
            $this->preparePostsForJson($this->postManager->findRecentPosts(20)), 200
        );
    }
    
    /**
     * Creates a new Post
     * @Route("/posts", name="npb_api_post_post")
     * @Method("POST")
     */
    public function postPostsAction(Request $request)
    {
        $post = new Post();
        $form = $this->createForm(new PostType(), $post);
        $requestParams = $this->decodeJsonRequest($request);
        
        $form->bind($this->filterRequestParamsForPost($requestParams));

        if ($form->isValid()) {
            $postManager = $this->getPostManager();
            $result = $postManager->savePost($post, $this->preparePostContent($post));
            
            if ($result instanceof Post) {
                return $this->prepareJsonResponse($this->preparePostForJson($result), 201);
            } elseif ($result instanceof \Exception) {
                # post not saved
                
                $this->get('logger')->error($result->getMessage());
                
                return $this->prepareJsonResponse($this->error500($result->getMessage()), 500);
            }
        } else {
            # invalid form
            return $this->prepareJsonResponse($this->error400($this->formErrorsToJson($form->getErrors())), 400);
        }
    }

    /**
     * Gets an existing Post
     * @Route("/posts/{id}", name="np_api_get_post")
     * @Method("GET")
     */
    public function getPostAction(Post $post)
    {
        return $this->responseHelper->prepareJsonResponse($this->preparePostForJson($post), 200);
    }
     
    /**
     * Updates an existing Post
     * @Route("/posts/{id}", name="npb_api_put_post")
     * @Method("PUT")
     */
    public function putPostAction(Request $request, $id)
    {
        $postManager = $this->getPostManager();
        $post = $postManager->findById($id);
        $requestParams = $this->decodeJsonRequest($request);
        
        if (!$post) return $this->prepareJsonResponse($this->error404(), 404);
        
        $form = $this->createForm(new PostType(), $post);
        $form->bind($this->filterRequestParamsForPost($requestParams));
        
        if ($form->isValid()) {
            $result = $postManager->savePost($post, $this->preparePostContent($post));
            if ($result instanceof Post) {
                return $this->prepareJsonResponse($this->preparePostForJson($post), 201);
            } elseif ($result instanceof \Exception) {
                $this->get('logger')->error('AG: '.$result->getMessage());
                return $this->prepareJsonResponse($this->error500($result->getMessage()), 500);
            }
        } else {
            return $this->prepareJsonResponse($this->error400($this->formErrorsToJson($form->getErrors())), 400);
        }
    }
    
    /**
     * @Route("/posts/{id}", name="npb_api_delete_post")
     * @Method("DELETE")
     */
    public function deletePostAction($id)
    {
        $postManager = $this->getPostManager();
        $post = $postManager->findById($id);
        
        if (!$post) return new Response(self::ERROR_404, 404);
        
        $status = $postManager->deletePost($post);
        
        if (true === $status) {
            return $this->prepareJsonResponse($this->prepareSuccess('Post Deleted', 'Post successfully deleted.'), 200);
        } else {
            return $this->prepareJsonResponse($this->error500('Post could not be deleted.'), 500);
        }
    }
    
    /**
     * @Route("/post-manager/publish/{id}", name="npb_api_publish_post")
     * @Method("POST")
     */
    public function publishPostAction(Request $request, $id)
    {
        $postManager = $this->getPostManager();
        $draftManager = $this->get(self::DRAFT_MANAGER);
        $post = $draftManager->findById($id);
        
        if (!$post) return new Response(self::ERROR_404, 404);
        
        # Get a new file path from the published post manager
        # and rename the draft's file, which will move it to the posts directory
        $result = $postManager->renamePostFile($post, $postManager->prepareFilePath($post));
        if ($result instanceof Post) {
            return $this->prepareJsonResponse($this->preparePostForJson($post), 201);
        } elseif ($result instanceof \Exception) {
            return $this->prepareJsonResponse($this->error500($result->getMessage()), 500);
        }
    }
    
    
    /***********************************************
     *               End of Actions                *
     ***********************************************/
    
    /**
     * Uses a twig template to format a post's content
     */
    protected function preparePostContent($post) {
        return $this->renderView('NodePubBlogBundle:Post:postSkeleton.html.twig', array(
            'post' => $post,
            'tags' => implode(', ', $post->tags->toArray())
        ));
    }
    
    protected function formErrorsToJson($errors) {
        $return = array();
        foreach ($errors as $error) {
            $return[] = $error->getMessageTemplate();
        }
        
        return json_encode($return);
    }
    
    
    /**
     * Backbone.js returns a bunch of extra params.
     * We filter out what we don't need or the form validation will choke.
     */
    protected function filterRequestParamsForPost(array $params)
    {
        $whitelist = array('slug', 'title', 'rawContent');
        $filteredParams = array();
        
        foreach ($whitelist as $key) {
            if (array_key_exists($key, $params)) {
                $filteredParams[$key] = $params[$key];
            }
        }
        
        return $filteredParams;
    }
    
    /**
     * Adds/removes properties on the post appropriate for API consumption.
     */
    protected function preparePostForJson(Post $post)
    {
        $post->links = array(
            'self' => $this->app['url_generator']->generate('np_api_get_post', array('id' => $post->id)),
            'preview' => $this->app['url_generator']->generate('np_preview_post'),
            'put' => $this->app['url_generator']->generate('np_api_put_post', array('id' => $post->id)),
            'delete' => $this->app['url_generator']->generate('np_api_delete_post', array('id' => $post->id))
        );
        
        if (isset($post->next->id)) {
            $post->links['nextPost'] = $this->app['url_generator']->generate('np_api_get_post', array('id' => $post->next->id));
        }
        
        if (isset($post->prev->id)) {
            $post->links['previousPost'] = $this->app['url_generator']->generate('np_api_get_post', array('id' => $post->prev->id));
        }
        
        $post->filteredContent = $post->getContent();
        
        // don't supply the full filepath or prev, next posts in api requests
        unset($post->filepath);
        
        if (isset($post->prev)) {
            unset($post->prev);
        }
        
        if (isset($post->next)) {
            unset($post->next);
        }
        
        return $post;
    }
    
    /**
     * Prepares a collection of Posts for API consumption.
     */
    protected function preparePostsForJson($posts)
    {
        return array_map(array($this, 'preparePostForJson'), $posts);
    }
}