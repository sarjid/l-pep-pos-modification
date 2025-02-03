<div class="topbar">

    <!-- LOGO -->
    <div class="topbar-left">
        <a href="" class="logo">
            <span>
                <img src="/{{ currentBranch()->logo }}" height="55px" alt="{{ $business_setting->name }}">
            </span>
            <i class="mdi mdi-layers"></i>
        </a>
    </div>


    <!-- Button mobile view to collapse sidebar menu -->
    <div class="navbar navbar-default" role="navigation">
        <div class="container-fluid">
            <h4 class="pl-2">{{ Auth::user()->name }}</h4>
            <!-- Page title -->
            <ul class="nav navbar-nav list-inline navbar-left">
                <li class="list-inline-item">
                    <button class="button-menu-mobile open-left">
                        <i class="mdi mdi-menu"></i>
                    </button>
                </li>
            </ul>

            <nav class="navbar-custom">

                <ul class="list-unstyled topbar-right-menu float-right mb-0">

                    <li>
                        <div class="notification-box mr-2">
                            <ul class="list-inline mb-0">
                                <li>
                                    <a href="javascript:void(0);" title="POS">
                                        <span id="topbarLink"><small>{{ __('sidebar.topbar')[0] }} :</small></span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>




                    @if (!isRole(ROLE_AGENT) && permission('ac1'))
                        <li>
                            <div class="notification-box mr-2">
                                <ul class="list-inline mb-0">
                                    <li>
                                        <a href="{{ route('account.receive.report') }}" title="Customer Receive">
                                            <span id="topbarLink1"><i class="fa fa-money"
                                                    style="font-size: 15px;"></i>&nbsp;<small
                                                    id="tobparlinktexthidden">{{ __('sidebar.topbar')[1] }}</small></span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                    @endif

                    @if (!isRole(ROLE_AGENT) &&permission('ac2'))
                        <li>
                            <div class="notification-box mr-2">
                                <ul class="list-inline mb-0">
                                    <li>
                                        <a href="{{ route('account.payment.report') }}" title="Supplier Payment">
                                            <span id="topbarLink2"><i class="fa fa-money"
                                                    style="font-size: 15px;"></i>&nbsp;<small
                                                    id="tobparlinktexthidden">{{ __('sidebar.topbar')[2] }}</small></span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                    @endif

                    @if(!isRole(ROLE_AGENT))
                        <li>
                            <div class="notification-box mr-2">
                                <ul class="list-inline mb-0">
                                    <li>
                                        <a href="javascript:void(0);" title="Today's Summery" id="summery">
                                            <span id="topbarLink3"><i class="ti-bar-chart"
                                                    style="font-size: 15px;"></i>&nbsp;<small
                                                    id="tobparlinktexthidden">{{ __('sidebar.topbar')[3] }}</small></span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                    @endif

                    @if(!isRole(ROLE_AGENT))
                        <li>
                            <div class="notification-box mr-2">
                                <ul class="list-inline mb-0">
                                    <li>
                                        <a href="{{ route('sale.in.return') }}" title="Today's Summery" id="">
                                            <span id="topbarLink5"><i class="fa fa-shopping-bag"
                                                    style="font-size: 15px;"></i>&nbsp;<small
                                                    id="tobparlinktexthidden">{{ __('sidebar.topbar')[5] }}</small></span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                    @endif

                    @if (permission('sa1'))
                        <li>
                            <div class="notification-box mr-4">
                                <ul class="list-inline mb-0">
                                    <li>
                                        <a href="{{ route('pos') }}" title="POS">
                                            <span id="topbarLink4"><i class="mdi mdi-cart-plus"
                                                    style="font-size: 15px;"></i>&nbsp;<small
                                                    id="tobparlinktexthidden">{{ __('sidebar.topbar')[4] }}</small></span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                    @endif
                    <li>
                        <!-- Notification -->
                        <div class="notification-box">
                            <ul class="list-inline mb-0">
                                <li>
                                    <a href="javascript:void(0);" class="right-bar-toggle">
                                        <i class="mdi mdi-account-settings"></i>
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <!-- End Notification bar -->
                    </li>




                </ul>
            </nav>
        </div><!-- end container -->
    </div><!-- end navbar -->
</div>
