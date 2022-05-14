import { Component, OnInit, Input, Output, EventEmitter, OnDestroy } from '@angular/core';
import { Router } from '@angular/router';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { LoginService } from './login.service';
import { ProductsService} from '../product.service';
import {DialogService} from 'primeng/dynamicdialog';
import {DynamicDialogRef} from 'primeng/dynamicdialog';
import { ForgotPassWord } from './forgot-password';


declare var $;
@Component({
  selector: 'app-login',
  templateUrl: './login.component.html',
  styleUrls: ['./login.component.css'],
  providers: [DialogService]
})
export class LoginComponent implements OnInit, OnDestroy {
  loginForm: FormGroup;
  isLoader = false;
  loginMsg: any;
  isFormSubmit = false;
  isRegActive = false;
  @Input() popUpLog: boolean;
  @Output() logRegShift = new EventEmitter();
  isTransperent = true;
  constructor(private formBuilder: FormBuilder, private loginService: LoginService, private router: Router,
              private productSearchServices: ProductsService, public dialogService: DialogService) { }
  ref: DynamicDialogRef;
  ngOnInit(): void {
    this.loginForm = this.formBuilder.group({
      email: ['', [Validators.required, Validators.email]],
      pwd: ['', Validators.required]
    });
  }
  ngOnDestroy(): void {
      if (this.ref) {
          this.ref.close();
      }
  }

  onSubmit() {
    debugger;
    this.isFormSubmit = true;
    const loginData = this.loginForm.value;
    if (!this.loginForm.get('email').valid){
    }else if (!this.loginForm.get('pwd').valid){
    }
    else{
      this.isLoader = true;
      this.loginService.login(this.loginForm.value)
        .subscribe((response): void => {
          if (typeof response !== 'undefined' && response !== null && response !== ''
              && (response.status === 'success' || response.token !== '')) {
            if (typeof response.token !== 'undefined' && response.token !== null && response.token !== ''
                && !this.popUpLog && response?.email_verified_status){
              localStorage.setItem('tocken', response.token);
              localStorage.setItem('userId', response.id);
              localStorage.setItem('userType', response.is_vendor);
              this.productSearchServices.getSiteSetting('/');
              this.isTransperent = false;
              this.productSearchServices.setNotificationData(response.token);
              /* this.loginForm.reset(); */
            }
            else if (typeof response.token !== 'undefined' && response.token !== null && response.token !== ''
                    && this.popUpLog && response?.email_verified_status){
              this.loginForm.reset();
              localStorage.setItem('tocken', response.token);
              localStorage.setItem('userId', response.id);
              localStorage.setItem('userType', response.is_vendor);
              this.productSearchServices.getSiteSetting(null);
              $('#popup-login').hide();
              $('#popup-cnt').hide(100);
              this.productSearchServices.setNotificationData(response.token);
            }
            else if (!response?.email_verified_status){
              this.loginMsg = {
                type: 'error',
                head: 'Error !',
                data: 'Not a verified user please check your mail and verify. Please try again.'
              };
            }
            else{
              this.loginMsg = {
                type: 'error',
                head: 'Error !',
                data: 'Login Failed. Please try again.'
              };
            }
          }
          else if (response.status === 'error'){
            this.isFormSubmit = false;
            this.loginForm.reset();
            this.loginMsg = {
              type: 'error',
              head: 'Error !',
              data: 'Login Failed. Please try again.'
            };
          }
          setTimeout(() => {
            this.isLoader = false;
            this.loginMsg = null;
          }, 3000);
          /* this.loginForm.reset();
            localStorage.setItem('tocken',response.token);
            this.onVendorLogin(); */

          /* this.isLoader = false; */
        },
          (error) => {
            if (error.status === 401){
              this.loginMsg = {
                type: 'error',
                head: 'Error !',
                data: 'Username or Password incorrect.'
              };
            }else{
              this.loginMsg = {
                type: 'error',
                head: 'Error !',
                data: 'Login Failed. Please try again.'
              };
            }
            this.isLoader = false;
          });
    }

      }
      onVendorLogin() {
        this.loginService.vendorLogin()
            .subscribe((response): void => {
              window.open(response);
            },
            (error) => {

            });
      }
      navigatToLogUrl(url, isPopup) {
        if (!isPopup){
          this.router.navigate([url]);
        }
        else{
          this.logRegShift.emit(true);
        }
      }
      closePopup() {
        $('#popup-login').hide();
      }
      forgotPwd() {

        this.ref = this.dialogService.open(ForgotPassWord, {
            header: 'Forgot Password',
            styleClass: 'p-col-12',
            width: 'auto',
            contentStyle: { 'max-height': '500px', overflow: 'auto'},
            baseZIndex: 10000
        });

        this.ref.onClose.subscribe((product) => {
        });
      }


}

