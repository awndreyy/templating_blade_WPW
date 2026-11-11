<?php

namespace App\Http\Controllers;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;

class ReceiptController extends Controller{
    public function print(){
        $printer = null;
        try {
            $path = storage_path('app/struk-test.bin');
            $connector = new FilePrintConnector($path);

            $printer = new Printer($connector);

            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->setEmphasis(true);
            $printer->text("TOKO ANAK LANANG\n");
            $printer->setEmphasis(false);

            $printer->text("Jl. In Aja Dulu No. 123\n");
            $printer->text("--------------------------\n");

            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text("Barang A      2 x 5000\n");
            $printer->text("Barang B      1 x 10000\n");
            $printer->text("--------------------------\n");
            $printer->text("TOTAL        Rp20.000\n");
            $printer->text("Terima kasih!\n");

            $printer->cut();
            $printer->close();
            $printer = null;

            return response()->json([
                'message' => 'Data struk berhasil dibuat',
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        } finally {
            if ($printer !== null) {
                $printer->close();
            }
        }
    }
}
