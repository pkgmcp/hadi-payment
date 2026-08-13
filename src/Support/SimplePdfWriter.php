<?php

declare(strict_types=1);

namespace Hadi\Payment\Support;

/**
 * Minimal, dependency-free PDF generator.
 *
 * Produces a single-page PDF with a title and a list of text lines using
 * the base-14 Helvetica font. Intended for simple report exports where a
 * full document library would be overkill.
 */
class SimplePdfWriter
{
    private const PAGE_WIDTH = 612.0;

    private const PAGE_HEIGHT = 792.0;

    private const MARGIN = 40.0;

    private const LINE_HEIGHT = 14.0;

    private string $title = '';

    private array $lines = [];

    public function title(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function addLine(string $line): self
    {
        $this->lines[] = $line;

        return $this;
    }

    public function output(): string
    {
        $content = $this->buildContentStream();
        [$body, $offsets] = $this->buildBody($content);

        $xrefOffset = strlen($body);

        return $body . $this->buildXref($offsets) . $this->buildTrailer($xrefOffset);
    }

    private function buildContentStream(): string
    {
        $lines = [];

        if ($this->title !== '') {
            $lines[] = 'BT /F2 18 Tf ' . self::MARGIN . ' ' . (self::PAGE_HEIGHT - 70) . " Td (" . $this->escape($this->title) . ") Tj ET\n";
        }

        $y = self::PAGE_HEIGHT - 100;
        foreach ($this->lines as $line) {
            $lines[] = 'BT /F1 10 Tf ' . self::MARGIN . ' ' . $y . " Td (" . $this->escape($line) . ") Tj ET\n";
            $y -= self::LINE_HEIGHT;
        }

        return implode('', $lines);
    }

    /**
     * @return array{0: string, 1: array<int, int>}
     */
    private function buildBody(string $content): array
    {
        $objects = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 ' . self::PAGE_WIDTH . ' ' . self::PAGE_HEIGHT . '] /Contents 4 0 R /Resources << /Font << /F1 5 0 R /F2 6 0 R >> >> >>',
            '<< /Length ' . strlen($content) . " >>\nstream\n" . $content . 'endstream',
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>',
        ];

        $body = "%PDF-1.4\n";
        $offsets = [];

        foreach ($objects as $index => $object) {
            $offsets[$index + 1] = strlen($body);
            $body .= ($index + 1) . " 0 obj\n" . $object . "\nendobj\n";
        }

        return [$body, $offsets];
    }

    /**
     * @param array<int, int> $offsets
     */
    private function buildXref(array $offsets): string
    {
        $xref = "xref\n0 6\n0000000000 65535 f \n";

        foreach (range(1, 5) as $objectNumber) {
            $xref .= sprintf("%010d 00000 n \n", $offsets[$objectNumber]);
        }

        return $xref;
    }

    private function buildTrailer(int $xrefOffset): string
    {
        return "trailer\n<< /Size 6 /Root 1 0 R >>\nstartxref\n{$xrefOffset}\n%%EOF\n";
    }

    private function escape(string $value): string
    {
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $value);
    }
}
