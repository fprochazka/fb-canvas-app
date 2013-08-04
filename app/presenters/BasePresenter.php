<?php

use Kdyby\Facebook\Dialog\LoginDialog;
use Kdyby\Facebook\FacebookApiException;
use Nette\Diagnostics\Debugger;



/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{
	use \Kdyby\Autowired\AutowireProperties;
	use \Kdyby\Autowired\AutowireComponentFactories;

	/**
	 * @var \Kdyby\Facebook\Facebook
	 * @autowire
	 */
	protected $facebook;



	protected function startup()
	{
		parent::startup();

		$this->getSession()->start();

		// when in canvas, the first request is POST
		if ($this->request->isPost() && $this->facebook->getUser() > 0) {
			try {
				$me = $this->facebook->api('/me');
				$this->user->login(new \Nette\Security\Identity($me->id, 'guest', $me));

			} catch (FacebookApiException $e) {
				Debugger::log($e, 'facebook');
			}
		}

		if (!$this->isSignalReceiver($this['fbLogin']) && !$this->user->isLoggedIn()) {
			$this['fbLogin']->open();
		}
	}



	protected function beforeRender()
	{
		parent::beforeRender();

		$this->template->facebookAppId = $this->facebook->config->appId;
	}



	/** @return LoginDialog */
	protected function createComponentFbLogin()
	{
		$dialog = $this->facebook->createDialog('login');
		/** @var LoginDialog $dialog */

		$dialog->onResponse[] = function (LoginDialog $dialog) {
			$fb = $dialog->getFacebook();

			if (!$fb->getUser()) {
				$this->flashMessage("Sorry bro, facebook authentication failed.");

				return;
			}

			try {
				$me = $fb->api('/me');
				$this->user->login(new \Nette\Security\Identity($me->id, 'guest', $me));

			} catch (FacebookApiException $e) {
				Debugger::log($e, 'facebook');
				$this->flashMessage("Sorry bro, facebook authentication failed hard.");
			}
		};

		return $dialog;
	}



	public function redirectUrl($url, $code = NULL)
	{
		if ($this->isAjax()) {
			$this->payload->redirect = (string) $url;
			$this->sendPayload();
		}

		$this->sendResponse(new CanvasRedirectResponse($url));
	}

}



class CanvasRedirectResponse extends Nette\Object implements \Nette\Application\IResponse
{

	/** @var string */
	private $url;



	/**
	 * @param $url
	 */
	public function __construct($url)
	{
		$this->url = (string) $url;
	}



	/**
	 * @return string
	 */
	final public function getUrl()
	{
		return $this->url;
	}



	/**
	 * Sends response to output.
	 */
	public function send(Nette\Http\IRequest $httpRequest, Nette\Http\IResponse $httpResponse)
	{
		echo "<script> top.location.href= " . Nette\Templating\Helpers::escapeJs($this->url) . "</script>\n";
		echo '<a href="' . Nette\Templating\Helpers::escapeHtml($this->url, ENT_QUOTES) . '">';
	}

}
