<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ConferenceControllerTest extends WebTestCase {

	public function testIndex() {
		$client = static::createClient();
		$client->request('GET', '/en/');

		$this->assertResponseIsSuccessful();
		$this->assertSelectorTextContains('h2', 'Give your feedback');
	}

	public function testConferencePage() {
		$client = static::createClient();
		$crawler = $client->request('GET', '/en/');

		$this->assertCount(2, $crawler->filter('h4'));

		$client->click($crawler->filter('h4 + p a')->link());
		//$client->clickLink('View');

		$this->assertPageTitleContains('Amsterdam');
		$this->assertResponseIsSuccessful();
		$this->assertSelectorTextContains('h2', 'Amsterdam 2019');
		$this->assertSelectorExists('p:contains("This was a great conference.")');
	}

	public function testCommentSubmission() {
		$client = static::createClient();

		$client->request('GET', '/en/conference/amsterdam-2019');
		$client->submitForm('Submit', [
			'comment_form[author]' => 'Fabien',
			'comment_form[text]' => 'Some feedback from an automated functional test',
			'comment_form[email]' => $email = 'me@automat.ed',
			'comment_form[photo]' => dirname(__DIR__, 2) . '/public/images/under-construction.gif',
		]);

		// simulate comment validation
		$comment = self::$container->get(CommentRepository::class)->findOneByEmail($email);
		$comment->setState('published');
		self::$container->get(EntityManagerInterface::class)->flush();

		$this->assertResponseRedirects();
		$client->followRedirect();
		$this->assertSelectorExists('p:contains("Some feedback from an automated functional test")');
	}
}