
  qz.security.setCertificatePromise(function(resolve, reject) {
      //Preferred method - from server
//        $.ajax("assets/signing/digital-certificate.txt").then(resolve, reject);

      //Alternate method 1 - anonymous
//        resolve();

      //Alternate method 2 - direct
      resolve("-----BEGIN CERTIFICATE-----\n" +
            "MIID9DCCAtygAwIBAgIJAPdXYKD3oGt9MA0GCSqGSIb3DQEBCwUAMIGOMQswCQYD\n"+
            "VQQGEwJQRTEQMA4GA1UECAwHVUNBWUFMSTERMA8GA1UEBwwIUFVDQUxMUEExDDAK\n"+
            "BgNVBAoMA1RFTDEMMAoGA1UECwwDVEVMMRowGAYDVQQDDBF0b2RvLWVuLWxpbmVh\n"+
            "LmNvbTEiMCAGCSqGSIb3DQEJARYTdGhlbmV3Nzc3QGdtYWlsLmNvbTAeFw0xODAy\n"+
            "MTMwNDEyMzFaFw00OTA4MDgwNDEyMzFaMIGOMQswCQYDVQQGEwJQRTEQMA4GA1UE\n"+
            "CAwHVUNBWUFMSTERMA8GA1UEBwwIUFVDQUxMUEExDDAKBgNVBAoMA1RFTDEMMAoG\n"+
            "A1UECwwDVEVMMRowGAYDVQQDDBF0b2RvLWVuLWxpbmVhLmNvbTEiMCAGCSqGSIb3\n"+
            "DQEJARYTdGhlbmV3Nzc3QGdtYWlsLmNvbTCCASIwDQYJKoZIhvcNAQEBBQADggEP\n"+
            "ADCCAQoCggEBAMJnB2YAxNj2pHo0a17Epaz+N9tnJ9zRTctxiqjb2N7LHNEIPTpv\n"+
            "4wmRNfybhJEKy0vaq+544eJm9eglJWlEg0Iq5OQrNwxaL2yKOIEi7NyXabWi8CFf\n"+
            "l3rUMh+EGu8hwunma8rlhH7KDJ5VZ4NGpnaPaQ5wEnswt3H3JeXAyRQNrbHe7HJB\n"+
            "+YfG/VFthLFNEhh35NDy8c7p0WN/pVp5BRspJOV9R8jrTXbBSC8s1R8Em4nruQhP\n"+
            "7COcdnAiQvDOIse3H78KhfMPPjGFGEIOoj1LnLjenw0BxjDQxUVrVbNGUx2Z0fvv\n"+
            "Y9Y7y4L2CeY8mFtWaXTtEbnKQBSnGtbCRXECAwEAAaNTMFEwHQYDVR0OBBYEFBJ5\n"+
            "wYUHD4TavETKkrJoypy2XhBRMB8GA1UdIwQYMBaAFBJ5wYUHD4TavETKkrJoypy2\n"+
            "XhBRMA8GA1UdEwEB/wQFMAMBAf8wDQYJKoZIhvcNAQELBQADggEBAE1q2eq1BdT8\n"+
            "jReUZpkOqAvkA1bxOQH2eTiFBYlFPk4ktylqXwcawsdNTlFdHvPTTcbqreDcIut9\n"+
            "PPqZZ3VzbdRC9mGWxC7NZOTK7DW9AoV3e6LweMdYqHPdZHg+ayxCqsmSX6AOTn0k\n"+
            "VW7g5/Av48PEX+UouPUFhujpOBNrKsU9DH9JrU8jbIAtbaofnD4zbYERi8Dyc88p\n"+
            "5E8KclUBOdega3CEI/VgHwc5iw8TvVXu6WO3B5WhkGBKNVc1mpwndM8GDBf0YwsB\n"+
            "SbMOGwAbAlyaS1F5X3+qlaDpQdH7XhisLxfCHs52Nz9dLG+sNAhacIDzBZlDB/sU\n"+
            "oW/Zf3zj9Ag=\n"+
            "-----END CERTIFICATE-----\n");
  });

  var privateKey = ""; // Signing requires a separately configured server-side signer; never embed a private key.


    qz.security.setSignaturePromise(function(toSign) {
        return function(resolve, reject) {
            try {
                var pk = KEYUTIL.getKey(privateKey);
                var sig = new KJUR.crypto.Signature({"alg": "SHA1withRSA"});
                sig.init(pk);
                sig.updateString(toSign);
                var hex = sig.sign();
                resolve(stob64(hextorstr(hex)));
            } catch (err) {
                reject(err);
            }
        };
    });
