<?php

interface FileReader {
	public function read( $path );
}

class MesajPaneliApi {
	private $actions;
	private $parametricMessages = [];

	/**
	 * MesajPaneli Client constructor.
	 *
	 * @param string $configFileName
	 *
	 * @throws ClientException
	 */
	public function __construct( $configFileName = 'app/inc/sms/config.json' ) {
		$this->actions = new UserActions( new Credentials( $configFileName ) );

		if ( ! $this->actions ) {
			throw new ClientException( "Müşteri bilgileri doğrulanamadı. Lütfen config.json dosyasını kontrol edin." );
		}
	}

	##### Kullanıcı Bilgileri Fonksiyonları #####

	/**
	 * User objesini döndürür.
	 *
	 * Beklenen array:
	 * $this->credentialsArray = [ 'name' => 'kullaniciAdi', 'pass' => 'sifre' ];
	 *
	 * Bilgiler doğru girildiğinde:
	 * {"userData":{"musteriid":"12345678","bayiid":"2415","musterikodu":"Demo","yetkiliadsoyad":"Demo","firma":"Demo","orjinli":"0","sistem_kredi":"0","basliklar":["850"]},"status":true}
	 *
	 * Bilgiler yanlış girildiğinde:
	 * {"status":false,"error":"Hatali kullanici adi, sifre girdiniz. Lutfen tekrar deneyiniz."}
	 *
	 * @return User
	 * @throws AuthenticationException
	 */
	public function getUser() {
		return $this->actions->getUser();
	}

	public function baslikliKrediSorgula() {
		return $this->actions->getUser()->getOriginatedBalance();
	}

	public function numerikKrediSorgula() {
		return $this->actions->getUser()->getNumericBalance();
	}

	public function kayitliBasliklar() {
		return $this->actions->getUser()->getSenders();
	}

	public function musteriID() {
		return $this->actions->getUser()->getMid();
	}


	/**
	 * Kullanıcı şifresi değiştirme metodu
	 *
	 * @param string $yeniSifre
	 *
	 * @return string
	 * @throws AuthenticationException
	 *
	 * Beklenen array:
	 * $this->credentialsArray = [ 'name' => 'kullaniciAdi', 'pass' => 'eskiSifre', 'newpass' => 'yeniSifre' ];
	 *
	 * */
	public function sifreDegistir( $yeniSifre ) {
		return $this->actions->resetPassword( $yeniSifre );
	}

	/**
	 * Hatalı kredi iade yapma metodu
	 *
	 * @param int $ref
	 *
	 * @return string
	 * @throws AuthenticationException
	 */
	public function hataliKrediIade( $ref ) {
		return $this->actions->refund( $ref );
	}

	##### Mesaj Gönderim Fonksiyonları #####

	/**
	 * Toplu mesaj gönderimi
	 *
	 * @param string $baslik
	 * @param TopluMesaj|array $data
	 * @param bool $tr
	 * @param null|int $gonderimZamani
	 *
	 * @return string
	 * @throws SmsException
	 */
	public function topluMesajGonder( $baslik, $data, $tr = false, $gonderimZamani = null ) {
		return $this->actions->bulkSMS( $baslik, $data, $tr, $gonderimZamani );
	}

	/**
	 * Parametrik mesaj gönderimi için gsm ve mesaj ekleme metodu.
	 * Bu fonksiyon ile gsm ve mesajları tek tek ekliyorsanız,
	 * parametrikMesajGonder fonksiyonunda $data arrayini null giriniz.
	 *
	 * @param string $gsm
	 * @param string $mesaj
	 *
	 * @return void
	 */
	public function parametrikMesajEkle( $gsm, $mesaj ) {
		$this->parametricMessages[] = [ 'tel' => $gsm, 'msg' => $mesaj ];
	}

	/**
	 * Parametrik mesaj gönderimi
	 *
	 * @param $baslik
	 * @param null|array $data
	 * @param bool $tr
	 * @param null|int $gonderimZamani
	 * @param bool $unique
	 *
	 * @return string
	 * @throws SmsException
	 */
	public function parametrikMesajGonder( $baslik, $data = null, $tr = false, $gonderimZamani = null, $unique = true ) {
		if ( is_null( $data ) ) {
			$data = $this->parametricMessages;
		}

		$response = $this->actions->parametricSMS( $baslik, $data, $tr, $gonderimZamani, $unique );

		$this->parametricMessages = [];

		return $response;
	}

	##### Rapor Alma Fonksiyonları #####

	/**
	 * Referans No ile rapor detayları
	 *
	 * @param $ref
	 * @param null|bool $tarihler
	 * @param null|bool $operatorler
	 *
	 * @return string
	 * @throws SmsException
	 */
	public function raporDetay( $ref, $tarihler = null, $operatorler = null ) {
		return $this->actions->reportDetails( $ref, $tarihler, $operatorler );
	}

	/**
	 * Tüm raporlar
	 *
	 * @param null|array $tarihler
	 * @param null|int $limit
	 *
	 * @return string
	 */
	public function raporListele( $tarihler = null, $limit = null ) {
		return $this->actions->listReports( $tarihler, $limit );
	}

	##### Telefon Defteri Fonksiyonları #####

	/**
	 * Tüm telefon defteri gruplarını getir
	 *
	 * @return string
	 * @throws AuthenticationException
	 */
	public function telefonDefteriGruplar() {
		return $this->actions->getAddressBooks();
	}

	/**
	 * Telefon defterine yeni grup ekle
	 *
	 * @param string $title
	 *
	 * @return string
	 * @throws AuthenticationException
	 */
	public function yeniGrup( $title ) {
		return $this->actions->createAddressBook( $title );
	}

	/**
	 * Telefon defterinden grup sil
	 *
	 * @param int $id
	 *
	 * @return string
	 * @throws AuthenticationException
	 */
	public function grubuSil( $id ) {
		return $this->actions->deleteAddressBook( $id );
	}

	/**
	 * Gruba kişi/numara ekle
	 *
	 * @param int $grupID
	 * @param array $numaralar
	 *
	 * @return string
	 * @throws AuthenticationException
	 */
	public function numaraEkle( $grupID, $numaralar ) {
		return $this->actions->addContact( $grupID, $numaralar );
	}

	/**
	 * Gruptan kişi/numara çıkar
	 *
	 * @param int $grupID
	 * @param array $numaralar
	 *
	 * @return string
	 * @throws AuthenticationException
	 */
	public function numaraCikar( $grupID, $numaralar ) {
		return $this->actions->removeContact( $grupID, $numaralar );
	}

	/**
	 * Gruba kayıtlı tüm kişiler
	 *
	 * @param int $grupID
	 *
	 * @return string
	 * @throws AuthenticationException
	 */
	public function gruptakiKisiler( $grupID ) {
		return $this->actions->getContactsByGroupID( $grupID );
	}

	/**
	 * Bir numarayı tüm gruplarda ara
	 *
	 * @param string $numara
	 *
	 * @return string
	 * @throws AuthenticationException
	 */
	public function tumGruplardaAra( $numara ) {
		return $this->actions->searchNumberInGroups( $numara );
	}

	/**
	 * Grupta bir numara ara
	 *
	 * @param string $numara
	 * @param int $grupID
	 *
	 * @return string
	 * @throws AuthenticationException
	 */
	public function gruptaAra( $numara, $grupID ) {
		return $this->actions->searchNumberInGroup( $numara, $grupID );
	}

	/**
	 * Telefon numarası girerek bir kişinin bilgilerini değiştir
	 *
	 * @param int $grupID
	 * @param int $numara
	 * @param array $degisiklikler
	 *
	 * @return string
	 * @throws AuthenticationException
	 */
	public function numaraIleKisiDuzenle( $grupID, $numara, $degisiklikler ) {
		return $this->actions->editContactByNumber( $grupID, $numara, $degisiklikler );
	}

	/**
	 * Kişi IDsi girerek kişi bilgilerini değiştir
	 *
	 * @param int $grupID
	 * @param int $kisiID
	 * @param array $degisiklikler
	 *
	 * @return string
	 * @throws AuthenticationException
	 */
	public function idIleKisiDuzenle( $grupID, $kisiID, $degisiklikler ) {
		return $this->actions->editContactById( $grupID, $kisiID, $degisiklikler );
	}
}

class UserActions {

	private $credentialsArray;

	private $endpoint;

	/**
	 * UserActions constructor.
	 *
	 * @param Credentials $credentials
	 */
	public function __construct( Credentials $credentials ) {
		$this->credentialsArray = $credentials->getAsArray();
		$this->endpoint = "api.mesajpaneli.com/json_api";
	}

	/**
	 * Add new group to address book
	 *
	 * @param string $title
	 *
	 * @return string
	 * @throws AuthenticationException
	 */
	public function createAddressBook( $title ) {

		if ( ! $title ) {
			throw new AuthenticationException( "Yeni grup ismi boş olamaz." );
		}

		if ( in_array( $title, $this->getUser()->getSenders() ) ) {
			throw new AuthenticationException( "Bu ($title) isimde bir grup zaten bulunmaktadır." );
		}

		$this->credentialsArray['groupName'] = $title;

		$base64Decoded = base64_decode( $this->doCurl( $this->endpoint . '/group/createGroup', $this->encode() ) );

		return $this->checkJSON( $base64Decoded );
	}

	/**
	 * Returns the user object
	 *
	 * Expected array:
	 * $this->credentialsArray = [ 'name' => 'kullaniciAdi', 'pass' => 'sifre' ];
	 *
	 * Successful response upon sending correct credentials:
	 * {"userData":{"musteriid":"12345678","bayiid":"2415","musterikodu":"Demo","yetkiliadsoyad":"Demo","firma":"Demo","orjinli":"0","sistem_kredi":"0","basliklar":["850"]},"status":true}
	 *
	 * Failed response upon wrong credentials:
	 * {"status":false,"error":"Hatali kullanici adi, sifre girdiniz. Lutfen tekrar deneyiniz."}
	 *
	 * @return User
	 * @throws AuthenticationException
	 */
	public function getUser() {
		$userInfo = json_decode( base64_decode( $this->doCurl( $this->endpoint . '/login', $this->encode() ) ), true );

		if ( ! $userInfo['status'] ) {
			$message = ( $userInfo['error'] !== '' ) ? $userInfo['error'] : 'Hatalı cevap alındı. Kullanıcı bilgilerini kontrol edin.';
			throw new AuthenticationException( $message );
		}

		return new User( $userInfo );
	}

	/**
	 * Curl request
	 *
	 * @param $endpoint
	 * @param $postFields
	 *
	 * @return string
	 */
	private function doCurl( $endpoint, $postFields ) {
		Curl::fetch( $endpoint,
			[
				CURLOPT_USERAGENT      => "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.90 Safari/537.36",
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_POST           => 1,
				CURLOPT_POSTFIELDS     => $postFields,
				CURLOPT_TIMEOUT        => 50,
				CURLOPT_ENCODING       => '',
				CURLOPT_HEADERFUNCTION => [ 'Curl', 'head' ],
				CURLOPT_WRITEFUNCTION  => [ 'Curl', 'body' ]
			]
		);

		return Curl::$body;
	}

	/**
	 * Encodes credentialsArray as data to be sent over Curl
	 *
	 * @return string
	 * @throws AuthenticationException
	 */
	private function encode() {
		if ( ! $this->credentialsArray ) {
			throw new AuthenticationException( "Giriş bilgilerinin config.json dosyasinda varlığını kontrol edin." );
		}

		return "data=" . base64_encode( json_encode( $this->credentialsArray ) );
	}

	/**
	 * Decode and check the JSON response
	 *
	 * @param string $base64Decoded
	 * @param null|string $column
	 *
	 * @return string
	 * @throws AuthenticationException
	 */
	private function checkJSON( $base64Decoded, $column = null ) {

		$decoded = json_decode( $base64Decoded, true );

		if ( json_last_error() || $decoded['status'] === false ) {
			throw new AuthenticationException( ( $decoded['error'] ) ? "Error: " . $decoded['error'] : 'Girilen bilgileri kontrol ediniz' );
		}

		if ( $column )
			$decoded = $decoded[ $column ];

		return ( json_last_error() ) ? "" : $decoded;
	}

	/**
	 * Remove a group from address book
	 *
	 * @param int $id
	 *
	 * @return string
	 * @throws AuthenticationException
	 */
	public function deleteAddressBook( $id ) {

		if ( ! $id ) {
			throw new AuthenticationException( "Grup id boş olamaz." );
		}

		if ( ! $this->searchForId( $id, $this->getAddressBooks() ) ) {
			throw new AuthenticationException( "Telefon defterinizde bu ($id) IDye sahip bir grup bulunmamaktadır." );
		}

		$this->credentialsArray['groupID'] = $id;

		$base64Decoded = base64_decode( $this->doCurl( $this->endpoint . '/group/deleteGroup', $this->encode() ) );

		return $this->checkJSON( $base64Decoded );
	}

	/**
	 * Search for value in multidimensional array
	 *
	 * @param $id
	 * @param $array
	 *
	 * @return bool|null
	 */
	private function searchForId( $id, $array ) {
		foreach ( $array as $key => $val ) {
			if ( $val['id'] == $id ) {
				return true;
			}
		}
		return null;
	}

	/**
	 * Returns all address books of logged in user
	 *
	 * @return string
	 * @throws AuthenticationException
	 */
	public function getAddressBooks() {
		$base64Decoded = base64_decode( $this->doCurl( $this->endpoint . '/group/getGroups', $this->encode() ) );

		return $this->checkJSON( $base64Decoded, 'groupList' );
	}

	/**
	 * Add a contact/phone number to an address book group
	 *
	 * @param int $groupID
	 * @param array $rows
	 *
	 * @return string
	 * @throws AuthenticationException
	 */
	public function addContact( $groupID, $rows ) {
		if ( ! $groupID || ! is_array( $rows ) || count( $rows ) < 1 ) {
			throw new AuthenticationException( "Kişi eklemek istediğiniz grup IDsi ve kişi bilgileri arrayini dolu gönderdiğinize emin olun." );
		}

		$this->credentialsArray['groupID'] = $groupID;
		$this->credentialsArray['rows'] = $rows;

		$base64Decoded = base64_decode( $this->doCurl( $this->endpoint . '/group/addContact', $this->encode() ) );

		return $this->checkJSON( $base64Decoded );
	}

	/**
	 * Remove a contact from an address book group
	 *
	 * @param int $groupID
	 * @param array $rows
	 *
	 * @return string
	 * @throws AuthenticationException
	 */
	public function removeContact( $groupID, $rows ) {
		if ( ! $groupID || ! is_array( $rows ) || count( $rows ) < 1 || ! isset( $rows['numara'] ) ) {
			throw new AuthenticationException( "Numara çıkarmak istediğiniz grup IDsi ve numara arrayini dolu gönderdiğinize emin olun." );
		}

		$this->credentialsArray['groupID'] = $groupID;
		$this->credentialsArray['numara'] = $rows['numara'];

		$base64Decoded = base64_decode( $this->doCurl( $this->endpoint . '/group/removeContact', $this->encode() ) );

		return $this->checkJSON( $base64Decoded );
	}

	/**
	 * Get all contacts in a group
	 *
	 * @param int $groupID
	 *
	 * @return string
	 * @throws AuthenticationException
	 */
	public function getContactsByGroupID( $groupID ) {
		if ( ! $groupID ) {
			throw new AuthenticationException( "Grup id boş olamaz." );
		}

		$this->credentialsArray['groupID'] = $groupID;

		$base64Decoded = base64_decode( $this->doCurl( $this->endpoint . '/group/getContactsByGroupID', $this->encode() ) );

		return $this->checkJSON( $base64Decoded, 'NumberList' );
	}

	/**
	 * Search a phone number in all address book groups
	 *
	 * @param string $number
	 *
	 * @return string
	 * @throws AuthenticationException
	 */
	public function searchNumberInGroups( $number ) {
		if ( ! $number ) {
			throw new AuthenticationException( "Aranacak numara boş olamaz." );
		}

		$this->credentialsArray['numara'] = $number;

		$base64Decoded = base64_decode( $this->doCurl( $this->endpoint . '/group/searchNumberInGroups', $this->encode() ) );

		return $this->checkJSON( $base64Decoded, 'NumberInfo' );
	}

	/**
	 * Search a phone number in an address book group
	 *
	 * @param string $number
	 * @param int $groupID
	 *
	 * @return string
	 * @throws AuthenticationException
	 */
	public function searchNumberInGroup( $number, $groupID ) {
		if ( ! $number || ! $groupID ) {
			throw new AuthenticationException( "Aranacak numara ve grup ID boş olamaz." );
		}

		$this->credentialsArray['numara'] = $number;
		$this->credentialsArray['groupID'] = $groupID;

		$base64Decoded = base64_decode( $this->doCurl( $this->endpoint . '/group/searchNumberInGroup', $this->encode() ) );

		return $this->checkJSON( $base64Decoded, 'NumberInfo' );
	}

	/**
	 * Edit contact details by phone number
	 *
	 * @param int $groupID
	 * @param string $number
	 * @param array $changes
	 *
	 * @return string
	 * @throws AuthenticationException
	 */
	public function editContactByNumber( $groupID, $number, $changes ) {
		if ( ! $number || ! $groupID || ! is_array( $changes ) || count( $changes ) < 1 ) {
			throw new AuthenticationException( "Grup IDsi, kişiye ait telefon numarası ve değiştirmek istediğiniz kişi bilgilerini dolu gönderdiğinize emin olun." );
		}

		$this->credentialsArray['groupID'] = $groupID;
		$this->credentialsArray['search'] = $number;
		$this->credentialsArray['changes'] = $changes;

		$base64Decoded = base64_decode( $this->doCurl( $this->endpoint . '/group/editContactByNumber', $this->encode() ) );

		return $this->checkJSON( $base64Decoded );
	}

	/**
	 * Edit contact details by id
	 *
	 * @param int $groupID
	 * @param int $contactID
	 * @param array $changes
	 *
	 * @return string
	 * @throws AuthenticationException
	 */
	public function editContactById( $groupID, $contactID, $changes ) {
		if ( ! $contactID || ! $groupID || ! is_array( $changes ) || count( $changes ) < 1 ) {
			throw new AuthenticationException( "Grup IDsi, kişi IDsi ve değiştirmek istediğiniz kişi bilgilerini dolu gönderdiğinize emin olun." );
		}

		$this->credentialsArray['groupID'] = $groupID;
		$this->credentialsArray['search'] = $contactID;
		$this->credentialsArray['changes'] = $changes;

		$base64Decoded = base64_decode( $this->doCurl( $this->endpoint . '/group/editContactById', $this->encode() ) );

		return $this->checkJSON( $base64Decoded );
	}

	/**
	 * Refund credits
	 *
	 * @param int $ref
	 *
	 * @return string
	 * @throws AuthenticationException Refund requires reference number
	 */
	public function refund( $ref ) {
		if ( ! $ref ) {
			throw new AuthenticationException( "Iade işlemi için referans no gereklidir." );
		}

		$this->credentialsArray['refno'] = $ref;

		$base64Decoded = base64_decode( $this->doCurl( $this->endpoint . '/refund', $this->encode() ) );

		return $this->checkJSON( $base64Decoded );
	}

	/**
	 * Reset user password
	 *
	 * Expected array:
	 * $this->credentialsArray = [ 'name' => 'username', 'pass' => 'oldPassword', 'newpass' => 'newPassword' ];
	 *
	 * @param string $newPassword
	 *
	 * @return string
	 * @throws AuthenticationException
	 *
	 * */
	public function resetPassword( $newPassword ) {
		if ( ! $newPassword ) {
			throw new AuthenticationException( "Yeni şifre boş olamaz." );
		}

		if ( $newPassword == $this->credentialsArray['user']['pass'] ) {
			throw new AuthenticationException( "Eski şifrenizden farklı bir yeni şifre seçiniz." );
		}

		$this->credentialsArray['user']['newpass'] = $newPassword;

		$base64Decoded = base64_decode( $this->doCurl( $this->endpoint . '/password', $this->encode() ) );

		return $this->checkJSON( $base64Decoded );
	}

	/**
	 * Send bulk SMS
	 *
	 * @param string $baslik
	 * @param TopluMesaj|array $data
	 * @param bool $tr
	 * @param null|int $gonderimZamani
	 *
	 * @return string
	 * @throws SmsException
	 */
	public function bulkSMS( $baslik, $data, $tr = false, $gonderimZamani = null ) {
		if ( ! $baslik ) {
			$baslik = '850';
		}

		if ( ! strlen( $baslik ) >= 3 ) {
			throw new SmsException( "Başlık minimum 3 karakterden oluşmalıdır." );
		}

		$this->credentialsArray['msgBaslik'] = $baslik;

		if ( ! $data ) {
			throw new SmsException( "SMS gönderilecek numaralar ve gönderilmek istenen mesajı doğru gönderdiğinize emin olun." );
		}

		if ( is_object( $data ) && get_class( $data ) == 'TopluMesaj' ) {
			$data = $data->getAsArray();
		}

		if ( ! is_array( $data ) || ! isset( $data['tel'] ) || ! isset( $data['msg'] ) || ! is_array( $data['tel'] ) ) {
			throw new SmsException( "SMS gönderilecek numaralar ve gönderilmek istenen mesajı doğru gönderdiğinize emin olun." );
		}

		$this->credentialsArray['msgData'][] = $data;

		if ( $gonderimZamani && $this->isValidTimeStamp( $gonderimZamani ) ) {
			$this->credentialsArray['start'] = $gonderimZamani;
		}

		if ( $tr ) {
			$this->credentialsArray['tr'] = $tr;
		}

		$base64Decoded = base64_decode( $this->doCurl( $this->endpoint . '/api', $this->encode() ) );

		return $this->checkJSON( $base64Decoded );
	}

	/**
	 * Checks if given timestamp is valid
	 *
	 * @param $timestamp
	 *
	 * @return bool
	 */
	private function isValidTimeStamp( $timestamp ) {
		return ( (string) (int) $timestamp === $timestamp )
			&& ( $timestamp <= PHP_INT_MAX )
			&& ( $timestamp >= ~PHP_INT_MAX );
	}

	/**
	 * Send Parametric SMS
	 *
	 * @param $baslik
	 * @param null|array $data
	 * @param bool $tr
	 * @param null|int $gonderimZamani
	 * @param bool $unique
	 *
	 * @return string
	 * @throws SmsException
	 */
	public function parametricSMS( $baslik, $data = null, $tr = false, $gonderimZamani = null, $unique = true ) {
		if ( ! $baslik ) {
			$baslik = '850';
		}

		if ( ! strlen( $baslik ) >= 3 ) {
			throw new SmsException( "Başlık minimum 3 karakterden oluşmalıdır." );
		}

		$this->credentialsArray['msgBaslik'] = $baslik;

		if ( ! $data || ! is_array( $data ) || ! count( $data ) ) {
			throw new SmsException( "SMS gönderilecek numaralar ve gönderilmek istenen mesajı doğru gönderdiğinize emin olun." );
		}

		$this->credentialsArray['msgData'] = $data;

		if ( $gonderimZamani && $this->isValidTimeStamp( $gonderimZamani ) ) {
			$this->credentialsArray['start'] = $gonderimZamani;
		}

		if ( $unique ) {
			$this->credentialsArray['unique'] = $unique;
		}

		if ( $tr ) {
			$this->credentialsArray['tr'] = $tr;
		}

		$base64Decoded = base64_decode( $this->doCurl( $this->endpoint . '/api', $this->encode() ) );

		return $this->checkJSON( $base64Decoded );
	}

	/**
	 * All reports
	 *
	 * @param null|array $tarihler
	 * @param null|int $limit
	 *
	 * @return string
	 */
	public function listReports( $tarihler = null, $limit = null ) {
		if ( $tarihler ) {
			$this->credentialsArray['tarih'] = $tarihler;
		}

		if ( $limit ) {
			$this->credentialsArray['limit'] = $limit;
		}

		$base64Decoded = base64_decode( $this->doCurl( $this->endpoint . '/report', $this->encode() ) );

		return $this->checkJSON( $base64Decoded );
	}

	/**
	 * Report details by reference id
	 *
	 * @param $ref
	 * @param null|bool $dates
	 * @param null|bool $operators
	 *
	 * @return string
	 * @throws SmsException
	 */
	public function reportDetails( $ref, $dates = null, $operators = null ) {
		if ( ! $ref ) {
			throw new SmsException( "Referans numarası gereklidir." );
		}

		if ( $dates ) {
			$this->credentialsArray['dates'] = $dates;
		}

		if ( $operators ) {
			$this->credentialsArray['operators'] = $operators;
		}

		$this->credentialsArray['refno'] = $ref;

		$base64Decoded = base64_decode( $this->doCurl( $this->endpoint . '/report', $this->encode() ) );

		return $this->checkJSON( $base64Decoded );
	}
}

class User {
	private $userInfo = [];

	public function __construct( $userInfo ) {
		if ( ! is_array( $userInfo ) || ! isset( $userInfo['userData'] ) ) {
			throw new AuthenticationException( "UserInfo array olmalıdır." );
		}

		$this->userInfo = $userInfo['userData'];
	}

	public function getMid() {
		return $this->userInfo['musteriid'];
	}

	public function getBid() {
		return $this->userInfo['bayiiid'];
	}

	public function getMik() {
		return $this->userInfo['musterikodu'];
	}

	public function getName() {
		return $this->userInfo['yetkiliadsoyad'];
	}

	public function getCompany() {
		return $this->userInfo['firma'];
	}

	public function getNumericBalance() {
		return $this->userInfo['sistem_kredi'];
	}

	public function getSenders() {
		return $this->userInfo['basliklar']; # array
	}

	public function getOriginatedBalance() {
		return $this->userInfo['orjinli'];
	}
}

class ConfigurationReader implements FileReader {

	public function read( $path ) {
		if ( is_readable( $path ) ) {
			# Remember that file_get_contents() will not work if your server has *allow_url_fopen* turned off.
			return file_get_contents( $path );
		}

		return false;
	}
}

class Credentials {
	private $username = '';
	private $password = '';
	private $endpoint = "api.mesajpaneli.com/json_api";

	public function __construct( $jsonFile ) {
		if ( $contents = ( new ConfigurationReader() )->read( $jsonFile ) ) {
			$data = json_decode( $contents, true );

			if ( ! $data ) {
				throw new \Exception( "JSON formatı bozuk bir dosyayı okumaya çalıştınız." );
			}

			$this->username = $data['user']['name'];
			$this->password = $data['user']['pass'];
		}

		$this->validate();
	}

	private function validate() {
		if ( ! $this->username || ! $this->password ) {
			throw new AuthenticationException( "Kullanıcı adı ve şifrenizi config.json dosyasında kontrol ediniz." );
		}

		$this->endpoint = ( strpos( $this->endpoint, 'http://' ) === 0 ) ? 'http://' . $this->endpoint : $this->endpoint;
	}

	public function getAsArray() {
		$this->validate();

		return [
			'user' => [
				'name' => $this->username,
				'pass' => $this->password
			]
		];
	}
}

class Curl {

	static $handle; // Handle
	static $body = ''; // Response body
	static $head = ''; // Response head
	static $info = [];

	static function head( $ch, $data ) {
		Curl::$head = $data;
		return strlen( $data );
	}

	static function body( $ch, $data ) {
		Curl::$body .= $data;
		return strlen( $data );
	}

	static function fetch( $url, $opts = [] ) {
		Curl::$head = Curl::$body = '';

		Curl::$info = [];
		Curl::$handle = curl_init( $url );
		curl_setopt_array( Curl::$handle, $opts );
		curl_exec( Curl::$handle );
		Curl::$info = curl_getinfo( Curl::$handle );
		curl_close( Curl::$handle );
	}
}

Class TopluMesaj {
	private $tel = [];
	private $msg;

	/**
	 * @param $mesajMetni
	 * @param array|string $numaralar
	 */
	public function __construct( $mesajMetni, $numaralar = '' ) {
		$this->msg = $mesajMetni;
		if ( is_array( $numaralar ) )
			$this->tel = $numaralar;
		else
			$this->tel = explode( ',', $numaralar );
	}

	public function numaraEkle( $gsm ) {
		$this->tel[] = $gsm;
	}

	public function getAsArray() {
		return [
			'tel' => $this->tel,
			'msg' => $this->msg
		];
	}
}

class AuthenticationException extends Exception {}

class ClientException extends Exception {}

class SmsException extends Exception {}