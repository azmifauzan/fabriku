<?php

namespace Tests\Feature\Storefront;

use App\Models\BusinessSite;
use App\Models\SiteContentPage;
use App\Models\SiteThemeVersion;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Storefront\HtmlSanitizerService;
use DOMDocument;
use DOMXPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DsfaHomemadeThemeTest extends TestCase
{
    use RefreshDatabase;

    private array $payload;

    private BusinessSite $site;

    private User $owner;

    private SiteThemeVersion $version;

    protected function setUp(): void
    {
        parent::setUp();
        $this->payload = require base_path('docs/new-plan/dsfa-homemade/theme.php');
        $tenant = Tenant::factory()->create(['name' => 'DSFA Homemade', 'business_category' => 'food']);
        $this->owner = User::factory()->forTenant($tenant->id)->create(['role' => 'admin']);
        $this->site = BusinessSite::factory()->draft()->create([
            'tenant_id' => $tenant->id, 'slug' => 'dsfa-homemade', 'mode' => 'produk',
            'custom_domain' => 'dsfahomemade.com', 'domain_status' => 'active',
            'profile' => ['name' => 'DSFA Homemade', 'whatsapp' => null, 'address' => null, 'logo_url' => 'https://fabriku.id/storage/logos/dsfa-homemade.png'],
            'seo' => $this->payload['theme']['satsetui_home_seo'],
        ]);
        $this->version = SiteThemeVersion::create([
            'tenant_id' => $tenant->id, 'business_site_id' => $this->site->id,
            'source' => 'satsetui', 'version' => 1, 'created_by' => $this->owner->id,
            ...$this->payload,
        ]);
        $this->site->update(['draft_theme_version_id' => $this->version->id]);
    }

    public function test_real_draft_is_private_and_its_css_survives_the_sanitizer(): void
    {
        $this->assertSame(trim($this->payload['theme']['satsetui_css']), app(HtmlSanitizerService::class)->sanitizeCss($this->payload['theme']['satsetui_css']));
        $this->get('/website/theme/'.$this->version->id.'/preview')->assertRedirect(route('login'));
        $preview = $this->actingAs($this->owner)->get('/website/theme/'.$this->version->id.'/preview')
            ->assertOk()->assertHeader('X-Robots-Tag', 'noindex, nofollow')
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertSee('Belum ada produk yang ditampilkan.')
            ->assertDontSee('href="#"', false)->assertDontSee('wa.me')->assertDontSee('<script', false);
        $this->assertSame(1, $this->dom($preview->getContent())->query('//h1')->length);
        $this->assertSame('draft', $this->site->fresh()->status);
        $this->assertNull($this->site->fresh()->active_theme_version_id);
        $otherOwner = User::factory()->manager()->create();
        BusinessSite::factory()->create(['tenant_id' => $otherOwner->tenant_id]);
        $this->actingAs($otherOwner)->get('/website/theme/'.$this->version->id.'/preview')->assertNotFound();
        $this->app['auth']->forgetGuards();
        foreach (['/', '/sitemap.xml', ...array_map(fn ($page) => '/halaman/'.$page['slug'], $this->payload['theme']['satsetui_pages'])] as $path) {
            $this->get('https://dsfahomemade.com'.$path)->assertNotFound();
            $this->get('https://dsfa-homemade.fabriku.biz.id'.$path)->assertNotFound();
        }
    }

    public function test_applying_the_real_draft_preserves_correct_pages_links_and_sitemap(): void
    {
        $this->actingAs($this->owner)->post('/website/satsetui/publish-draft')->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame('draft', $this->site->fresh()->status);
        $this->assertSame($this->version->id, $this->site->fresh()->active_theme_version_id);
        $this->assertNull($this->site->fresh()->draft_theme_version_id);
        $this->assertSame(6, $this->site->contentPages()->count());
        $this->site->update(['status' => 'published']);
        $this->app['auth']->forgetGuards();
        $documents = [];
        foreach (['/', '/produk', '/keranjang', '/privasi', '/lapor', ...array_map(fn ($page) => '/halaman/'.$page['slug'], $this->payload['theme']['satsetui_pages'])] as $path) {
            $response = $this->get('https://dsfahomemade.com'.$path)->assertOk()
                ->assertDontSee('href="#"', false)->assertDontSee('<script', false);
            $documents[$path] = $response->getContent();
        }
        foreach ($this->payload['theme']['satsetui_pages'] as $page) {
            $path = '/halaman/'.$page['slug'];
            $dom = $this->dom($documents[$path]);
            $this->assertSame(1, $dom->query('//h1')->length, $path);
            $this->assertSame($page['seo_title'], $dom->evaluate('string(//title)'), $path);
            $this->assertSame($page['seo_description'], $dom->evaluate('string(//meta[@name="description"]/@content)'), $path);
            $this->assertSame('https://dsfahomemade.com'.$path, $dom->evaluate('string(//link[@rel="canonical"]/@href)'), $path);
            $this->assertSame($page['html'], $this->site->contentPages()->where('slug', $page['slug'])->firstOrFail()->html);
        }
        foreach ($documents as $path => $html) {
            foreach ($this->dom($html)->query('//a/@href') as $href) {
                $url = parse_url($href->nodeValue);
                if (isset($url['host'])) {
                    $this->assertSame('fabriku.id', $url['host']);

                    continue;
                }
                $target = $url['path'] ?? $path;
                $this->assertArrayHasKey($target, $documents, $path.': '.$href->nodeValue);
                if (isset($url['fragment'])) {
                    $this->assertGreaterThan(0, $this->dom($documents[$target])->query('//*[@id="'.$url['fragment'].'"]')->length, $href->nodeValue);
                }
            }
        }
        $sitemap = $this->get('https://dsfahomemade.com/sitemap.xml')->assertOk();
        foreach ($this->payload['theme']['satsetui_pages'] as $page) {
            $sitemap->assertSee('https://dsfahomemade.com/halaman/'.$page['slug'], false);
        }
        $this->site->contentPages()->where('slug', 'cara-pesan')->firstOrFail()->update(['is_published' => false]);
        $otherSite = BusinessSite::factory()->create();
        SiteContentPage::create(['tenant_id' => $otherSite->tenant_id, 'business_site_id' => $otherSite->id, 'slug' => 'other-private-page', 'title' => 'Other tenant', 'html' => '<h1>Other tenant</h1>', 'is_published' => true]);
        $this->get('https://dsfahomemade.com/halaman/cara-pesan')->assertNotFound();
        $this->get('https://dsfahomemade.com/halaman/other-private-page')->assertNotFound();
        $this->get('https://dsfahomemade.com/sitemap.xml')->assertDontSee('/halaman/cara-pesan')->assertDontSee('other-private-page');
        $this->get('https://dsfa-homemade.fabriku.biz.id/halaman/jual-takoyaki-bandung?ref=menu')->assertStatus(301)->assertRedirect('https://dsfahomemade.com/halaman/jual-takoyaki-bandung?ref=menu');
        if ($output = getenv('DSFA_RENDER_OUTPUT')) {
            file_put_contents($output, json_encode($documents, JSON_THROW_ON_ERROR));
        }
    }

    private function dom(string $html): DOMXPath
    {
        $document = new DOMDocument;
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="UTF-8">'.$html);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        return new DOMXPath($document);
    }
}
