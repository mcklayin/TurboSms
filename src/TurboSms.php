<?php namespace Newway\TurboSms;


use Illuminate\Container\Container;
use Newway\TurboSms\Exceptions\TurboSmsException;
use SoapClient;

/**
 * Class TurboSms
 * @package Newway\TurboSms
 */
class TurboSms
{

    /**
     * The IoC Container
     *
     * @var Container
     */
    protected $app;

    /**
     * Build a new Theme manager
     *
     * @param Container $app
     */
    public function __construct(Container $app)
    {

        $this->app = $app;

        header ('Content-type: text/html; charset=utf-8');

        $this->client = $this->_getClient();

        $this->sender = $this->app['config']->get('turbo_sms.sender');

    }

    /**
     * Отправка одного сообщения
     *
     * Хоть массовая отправка и поддерживается АПИ
     * Мы ее реализовывать не будем в основном из-за неудобного получения статуса и ИД каждого сообщения
     *
     *
     * @param $text - текст сообщения
     * @param $phone - номер телефона получателя
     * @return string - код сообщения у провайдера
     *
     * @throws TurboSmsException
     */
    public function send($text, $phone)
    {

        $result = $this->client->SendSMS(
                [
                        'sender'      => $this->sender,
                        'destination' => $phone,
                        'text'        => $text
                ]
        );

        if ($result->SendSMSResult->ResultArray[0] != 'Сообщения успешно отправлены') {
            throw new TurboSmsException($result->SendSMSResult->ResultArray[0]);
        }

        // надеемся, что в ответе приходит
        return $result->SendSMSResult->ResultArray[1];
    }




    /**
     * Получение остатка кредитов
     *
     * @return int
     * @throws TurboSmsException
     */
    public function getBalance()
    {

        $result = $this->client->GetCreditBalance();

        // в спеке на запрос возвращается строка.
        // в саппорте говорят, что баланс в кредитах может быть дробным (о_О)
        // будем оперировать целым остатком.

        if (isset($result->GetCreditBalanceResult)){
            return intval($result->GetCreditBalanceResult);
        }

        throw new TurboSmsException('Cannot get balance');
    }

    /**
     * Статус сообщения
     *
     * @param $messageId
     * @return mixed
     * @throws TurboSmsException
     */
    public function getStatus($messageId)
    {

        $result = $this->client->GetMessageStatus(['MessageId' => $messageId]);


        // ответ на статус - строка.
        // т.е. чтоб реально узнать статус сообщение саппорт предлагает парсить строку и анализировать ее

        if (isset($result->GetMessageStatusResult)){
            return $result->GetMessageStatusResult;
        }

        throw new TurboSmsException('Cannot get message status');
    }



    /**
     * Врзвращаем клиент после авторизации
     *
     * @return SoapClient
     * @throws TurboSmsException
     */
    private function _getClient() {

        $url = $this->app['config']->get('turbo_sms.url', 'http://turbosms.in.ua/api/wsdl.html');

        $client = new SoapClient ($url);

        // Данные авторизации
        $credentials = $this->app['config']->get('turbo_sms.auth', []);

        if (empty($credentials['login']) || empty($credentials['password'])) {
            throw new TurboSmsException('Enter login and password from Turbosms');
        }

        // Авторизируемся на сервере
        $result = $client->Auth($credentials);

        // @todo - может как-то адекватно статус отдавать и соотв. проверять его?
        if (0 && $result->AuthResultCode != 0) {
            throw new TurboSmsException($result->AuthResult);
        }

        // Пока будем проверять так
        // Надеюсь тех. специалисты прислушаются и доделают АПИ
        if ($result->AuthResult . '' != 'Вы успешно авторизировались') {
           throw new TurboSmsException($result->AuthResult);
        }

        return $client;

    }








}
 