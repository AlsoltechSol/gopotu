<?php

namespace App\Http\Controllers;

use App\Model\Order;
use Illuminate\Http\Request;

class InvoicesCotntroller extends Controller
{
    public function generate($type, $id = "none")
    {
        $PDF_NAME = "document.pdf";

        switch ($type) {
            case 'order':
                $order = Order::findorfail(decrypt($id));
                // $order = Order::findorfail($id);

                if (!in_array($order->status, ['delivered'])) {
                    abort(403, 'Invoice Not Generated');
                }

                $view = 'order';
                $data['order'] = $order;
                $PDF_NAME = $order->code . '.pdf';
                break;

            default:
                abort(404);
                break;
        }

        $pdf = \PDF::loadView('invoices.' . $view, $data)->setPaper('A3', 'Portrait');
        return $pdf->stream($PDF_NAME);

        return view('invoices.' . $view, $data);
    }
}
