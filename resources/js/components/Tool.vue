<template>
  <LoadingView :loading="loading">
  <heading class="mb-6">Zwei-Faktor Authentifizierung</heading>
    <LoadingCard :loading="loading" class="tw-p-4">

      <div class="tw-grid tw-grid-cols-2 tw-gap-4">
        <div class="">

          <div class="" v-if="status.confirmed == 1">
            <p class="mb-4">Aktualisieren Sie Ihre Zwei-Faktor-Sicherheitseinstellungen</p>

            <div class="tw-flex tw-items-center tw-mb-4">
              <input v-model="status.enabled" :value="1" id="op-enable" type="radio"  class="tw-w-4 tw-h-4 tw-border-gray-300 tw-focus:ring-2 tw-focus:ring-blue-300">
              <label for="op-enable" class="tw-block tw-ml-2 tw-text-sm tw-font-medium">
                Aktivieren
              </label>
            </div>

            <div class="tw-flex tw-items-center tw-mb-4">
              <input v-model="status.enabled" :value="0" id="op-disable" type="radio" class="tw-w-4 tw-h-4 tw-border-gray-300 tw-focus:ring-2 tw-focus:ring-blue-300">
              <label for="op-disable" class="tw-block tw-ml-2 tw-text-sm tw-font-medium">
                Deaktivieren
              </label>
            </div>


            <br>

            <LoadingButton @click="toggle" >Einstellungen aktualisieren</LoadingButton>
            <LoadingButton class="tw-mx-3 tw-bg-red-700 hover:tw-bg-red-800 focus:tw-ring-4 focus:tw-ring-red-300 dark:tw-bg-red-600 dark:hover:tw-bg-red-700 dark:focus:tw-ring-red-900" @click="reset" >2FA zurücksetzen</LoadingButton>

          </div>

          <div v-else class="">
            <p class="tw-mb-8">
                Die Zwei-Faktor-Authentifizierung (2FA) erhöht die Zugriffssicherheit, indem zwei Methoden (auch als Faktoren bezeichnet) zur Überprüfung Ihrer Identität erforderlich sind. Die Zwei-Faktor-Authentifizierung schützt vor Phishing, Social Engineering und Brute-Force-Angriffen mit Passwörtern und schützt Ihre Anmeldungen vor Angreifern, die schwache oder gestohlene Anmeldeinformationen ausnutzen.
            </p>


            <div class="tw-mb-8">
                <h3 class="tw-my-2 tw-text-xl">Typ 2FA</h3>
                <div class="tw-flex tw-items-center tw-mb-4">
                    <input
                        id="email"
                        type="radio"
                        name="type"
                        value="email"
                        class="tw-w-4 tw-h-4 tw-border-gray-300 tw-focus:ring-2 tw-focus:ring-blue-300 tw-dark:focus:ring-blue-600 tw-dark:focus:bg-blue-600 tw-dark:bg-gray-700 tw-dark:border-gray-600"
                        v-model="form.type"
                    >
                    <label for="email" class="tw-block tw-ml-2 tw-text-sm tw-font-medium">
                        E-Mail
                    </label>
                </div>

                <div class="tw-flex tw-items-center tw-mb-4">
                    <input
                        id="google"
                        type="radio"
                        name="type"
                        value="google"
                        class="tw-w-4 tw-h-4 tw-border-gray-300 tw-focus:ring-2 tw-focus:ring-blue-300 tw-dark:focus:ring-blue-600 tw-dark:focus:bg-blue-600 tw-dark:bg-gray-700 tw-dark:border-gray-600"
                        v-model="form.type"
                    >
                    <label for="google" class="tw-block tw-ml-2 tw-text-sm tw-font-medium">
                        Google 2FA
                    </label>
                </div>
            </div>

            <div v-if="form.type === 'email'">
                <div class="tw-my-4 tw-text-md">
                    Klicken Sie auf "E-Mail erhalten" und geben Sie OTP aus der Nachricht ein, um 2FA zu aktivieren

                    <p><a class="tw-text-blue-700" @click.prevent="sendOtpEmail()" href="#">E-Mail erhalten</a></p>

                    <input v-model="form.otp"
                           @keyup="autoSubmit()"
                           placeholder="Enter OTP here" type="text"
                           class="form-control form-input form-input-bordered tw-my-4">
                    <br>
                </div>
            </div>

            <div v-if="form.type === 'google'">
                <h3 class="tw-my-2 tw-text-xl">Wiederherstellungscodes</h3>

                <p class="tw-mb-3">
                    Wiederherstellungscode wird verwendet, um auf Ihr Konto zuzugreifen, falls Sie kein Zwei-Faktor-Konto erhalten können
                    authentifizierungscodes.
                </p>
                <span class="tw-bg-gray-100 tw-text-gray-800 tw-text-xs tw-font-semibold tw-mr-2 tw-px-2.5 tw-py-0.5 tw-rounded ">Schritt 01</span>
                <p class="no-print tw-my-4 tw-text-md">
                    Laden Sie Ihren Wiederherstellungscode herunter, drucken oder kopieren Sie ihn, bevor Sie mit der Einrichtung der Zwei-Faktor-Authentifizierung fortfahren.
                </p>

                <div class="tw-mb-4 tw-border-dashed tw-border-2 tw-border-light-blue tw-p-4 tw-rounded-lg tw-text-center tw-bg-gray-50">
                    <h2 class="tw-text-xl tw-text-black">{{ twofa.recovery }}</h2>
                    <a class="tw-text-blue-700" @click.prevent="downloadAsText('recover_code.txt', twofa.recovery)" href="#">Herunterladen</a>
                </div>


                <span class="tw-bg-gray-100 tw-text-gray-800 tw-text-xs tw-font-semibold tw-mr-2 tw-px-2.5 tw-py-0.5 tw-rounded ">Schritt 02</span>

                <div class="tw-my-4 tw-text-md">
                    Scannen Sie diesen QR-Code mit Google Authenticator, um OTP einzurichten und einzugeben, um 2FA zu aktivieren

                    <input v-model="form.otp" @keyup="autoSubmit()" placeholder="Enter OTP here" type="text"
                           class="form-control form-input form-input-bordered tw-my-4">
                    <br>
                </div>
            </div>

            <div v-if="form.type">
                <LoadingButton :loading="loading" :disabled="loading" @click="confirmOtp" class="btn btn-default btn-primary">2FA aktivieren</LoadingButton>
            </div>

          </div>

        </div>

        <div class="tw-h-full" v-if="form.type === 'google'">
          <div v-if="!status.confirmed" class="tw-flex tw-justify-center tw-content-center tw-w-full tw-p-8">
            <img width="300" :src="twofa.google2fa_url" alt="qr_code">
          </div>
        </div>
      </div>


    </LoadingCard>
  </LoadingView>
</template>

<script>
export default {
  data() {
    return {
      twofa: [],
      form: {},
      status: null,
      loading: true,
    }
  },

  mounted() {
    this.getStatus()
    this.getRecoveryCodes()
  },

  methods: {
    getStatus() {
      Nova.request().get('/nova-vendor/nova-two-factor/status')
          .then(res => {
            this.status = res.data
            this.loading = false
          })
    },

    getRecoveryCodes() {
      Nova.request().get('/nova-vendor/nova-two-factor/register')
          .then(res => {
            this.twofa = res.data
          })
    },

    toggle() {
      Nova.request().post('/nova-vendor/nova-two-factor/toggle', {
        status: this.status.enabled
      })
          .then(res => {
            Nova.success(res.data.message)
          })
    },

    reset() {
        Nova.request().post('/nova-vendor/nova-two-factor/reset-2fa')
            .then(res => {
                this.getStatus()
                Nova.success(res.data.message)
            })
    },

    confirmOtp() {
      Nova.request().post('/nova-vendor/nova-two-factor/confirm', this.form)
          .then(res => {

           Nova.success(res.data.message)
            this.getStatus()
          })
          .catch(err => {
            Nova.error(err.response.data.message)
          })
    },

    autoSubmit() {
      if (this.form.otp.length === 6) {
        this.confirmOtp()
      }
    },

    downloadAsText(filename, text) {
      var element = document.createElement('a');
      element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(text));
      element.setAttribute('download', filename);

      element.style.display = 'none';
      document.body.appendChild(element);

      element.click();

      document.body.removeChild(element);
    },

    sendOtpEmail() {
        Nova.request().post('/nova-vendor/nova-two-factor/send-otp-email')
        .then(res => {
            Nova.success(res.data.message)
            this.getStatus()
        })
    }
  },

  computed: {
    //
  }
}
</script>
