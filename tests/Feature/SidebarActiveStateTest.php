<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Sidebar hanya punya satu link "Laporan Peminjaman" (href ke
 * transactions.report) yang menaungi dua tab — Riwayat (transactions.report)
 * dan Rekap (transactions.recap) — jadi link itu harus aktif untuk kedua
 * route tersebut, sementara "Peminjaman" (transactions.index) tidak boleh
 * ikut aktif di halaman laporan/rekap, dan sebaliknya.
 */
class SidebarActiveStateTest extends TestCase
{
    use RefreshDatabase;

    private User $staff;

    protected function setUp(): void
    {
        parent::setUp();

        $this->staff = User::factory()->create(['role' => 'staff']);
    }

    /**
     * Cari tag <a href="$href" class="...">, lalu cek apakah "active" ada di
     * daftar class-nya — jauh lebih diskriminatif daripada assertSee('active')
     * yang bisa saja cocok dengan class di link lain manapun di halaman.
     */
    private function sidebarLinkIsActive(string $html, string $href): bool
    {
        $found = preg_match(
            '#<a\s+href="' . preg_quote($href, '#') . '"\s+class="([^"]*)"#',
            $html,
            $matches
        );

        $this->assertSame(1, $found, "Link sidebar untuk {$href} tidak ditemukan di HTML.");

        $classes = preg_split('/\s+/', trim($matches[1]));

        return in_array('active', $classes, true);
    }

    public function test_recap_page_activates_laporan_peminjaman_link(): void
    {
        $html = $this->actingAs($this->staff)->get(route('transactions.recap'))->getContent();

        $this->assertTrue($this->sidebarLinkIsActive($html, route('transactions.report')), 'Laporan Peminjaman harus aktif saat tab Rekap dibuka.');
        $this->assertFalse($this->sidebarLinkIsActive($html, route('transactions.index')), 'Peminjaman TIDAK boleh ikut aktif.');
    }

    // Behavior existing — jangan sampai fix di atas malah merusak dua ini.

    public function test_transactions_index_still_activates_only_peminjaman_link(): void
    {
        $html = $this->actingAs($this->staff)->get(route('transactions.index'))->getContent();

        $this->assertTrue($this->sidebarLinkIsActive($html, route('transactions.index')), 'Peminjaman harus aktif.');
        $this->assertFalse($this->sidebarLinkIsActive($html, route('transactions.report')), 'Laporan Peminjaman tidak boleh ikut aktif.');
    }

    public function test_transactions_report_still_activates_only_laporan_link(): void
    {
        $html = $this->actingAs($this->staff)->get(route('transactions.report'))->getContent();

        $this->assertTrue($this->sidebarLinkIsActive($html, route('transactions.report')), 'Laporan Peminjaman harus aktif.');
        $this->assertFalse($this->sidebarLinkIsActive($html, route('transactions.index')), 'Peminjaman tidak boleh ikut aktif.');
    }
}
