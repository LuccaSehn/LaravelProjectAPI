<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class APIController extends Controller
{
    private function execCurl($url)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response, true);
    }

    public function listClientsLowestTotal()
    {
        try {
            $curlData = $this->execCurl('http://www.mocky.io/v2/5e960a2d2f0000f33b0257c4');
            usort($curlData, function ($a, $b) {
                // Ordena o array de acordo com o valorTotal do menor valor para o maior
                return $a['valorTotal'] < $b['valorTotal'] ? -1 : 1;
            });
            $return = array();
            foreach ($curlData as $v) {
                if (isset($return[$v['cliente']])) {
                    // Se achar duplicado, segue
                    continue;
                }
                // Se não achar duplicado, insere no array
                $return[$v['cliente']] = $v;
            }
            return response()->json($return, 201);
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function listClientsBiggestBuy()
    {
        try {
            $curlData = $this->execCurl('http://www.mocky.io/v2/5e960a2d2f0000f33b0257c4');
            $moreExpensiveItems = array();
            $return = array();
            foreach ($curlData as $data) {
                // Filtra dados onde a data for igual a 2019
                $date = explode('-', $data['data']);
                if ($date[2] == '2019') {
                    foreach ($data['itens'] as $item) {
                        // Verifica se $item['preco'] é maior que o valor $moreExpensiveItems[$data['cliente']], caso verdade, substitui
                        $moreExpensiveItems[$data['cliente']] = empty($moreExpensiveItems[$data['cliente']]) || ($item['preco'] > $moreExpensiveItems[$data['cliente']]) ? $item['preco'] : $moreExpensiveItems[$data['cliente']];
                    }
                }
            }
            // Ordena um array em ordem descrescente mantendo a associação entre índices e valores
            arsort($moreExpensiveItems);
            foreach ($moreExpensiveItems as $index => $moreExpensiveItem) {
                array_push($return, array('client' => $index, 'value' => $moreExpensiveItem));
            }
            return response()->json($return, 201);
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function listClientsMostBuys()
    {
        try {
            $curlData = $this->execCurl('http://www.mocky.io/v2/5e960a2d2f0000f33b0257c4');
            $clients2018 = array();
            $return = array();
            foreach ($curlData as $data) {
                // Filtra dados onde a data for igual a 2018
                $date = explode('-', $data['data']);
                if ($date[2] == '2018') {
                    array_push($clients2018, $data['cliente']);
                }
            }
            // Inverte as chaves e valores do array
            $clients2018 = array_flip($clients2018);
            foreach ($clients2018 as $index => $moreExpensiveItem) {
                array_push($return, array('client' => $index, 'value' => $moreExpensiveItem));
            }
            return response()->json($return, 201);
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function recommendClothes(Request $request)
    {
        try {
            $request->validate([
                'client_cpf' => 'required',
            ]);
            $curlData = $this->execCurl('http://www.mocky.io/v2/5e960a2d2f0000f33b0257c4');
            $clientItems = array();
            $totalItems = 0;
            $totalItemPrice = 0;
            foreach ($curlData as $shopHistoryData) {
                // Cria um array com todos os itens já comprados pelo cliente
                if ($shopHistoryData['cliente'] === $request->client_cpf) {
                    foreach ($shopHistoryData['itens'] as $item) {
                        array_push($clientItems, $item);
                        $totalItemPrice += $item['preco'];
                        $totalItems++;
                    }
                }
            }
            // Cálculo para determinar a média de preço
            $avgPrice = $totalItemPrice / $totalItems;
            foreach ($clientItems as $item) {
                if ($item['preco'] < $avgPrice) {
                    // Retorna um item com preço menor que a média
                    $return = $item;
                    break;
                }
            }
            return response()->json($return, 201);
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 500);
        }

    }
}
