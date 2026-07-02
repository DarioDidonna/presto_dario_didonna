<section class="presto-activity-section py-5 position-relative overflow-hidden" id="liveActivitySection">
    <div class="container position-relative z-index-2">
        <div class="row align-items-center gy-5">

            <div class="col-12 col-lg-5">
                <div class="d-inline-flex align-items-center gap-2 activity-pre-title px-3 py-1 rounded-pill mb-3">
                    <span class="live-pulse-dot"></span>
                    <span class="text-uppercase tracking-wider fs-8 fw-bold text-white-50">{{ __('ui.live_pulse_network') }}</span>
                </div>
                <h2 class="fw-black text-white text-uppercase tracking-tight display-5 mb-4">
                    {{ __('ui.market_in_motion') }} <br><span class="text-glow text-neon-cyan">{{ __('ui.constant_movement') }}</span>
                </h2>
                <p class="text-muted-custom fs-6 mb-4">
                    {{ __('ui.market_description') }}
                </p>

                <div class="row g-3 pt-2 border-top border-secondary-subtle">
                    <div class="col-6">
                        <div class="stat-number font-monospace fw-bold text-neon-cyan fs-3">1.2s</div>
                        <div class="text-muted-custom fs-8 text-uppercase tracking-wide">{{ __('ui.avg_response_time') }}</div>
                    </div>
                    <div class="col-6">
                        <div class="stat-number font-monospace fw-bold text-neon-amber fs-3">+340</div>
                        <div class="text-muted-custom fs-8 text-uppercase tracking-wide">{{ __('ui.interactions_last_hour') }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-7">
                <div class="radar-container position-relative mx-auto d-flex align-items-center justify-content-center">

                    <div class="radar-circle circle-1"></div>
                    <div class="radar-circle circle-2"></div>
                    <div class="radar-circle circle-3"></div>
                    <div class="radar-sweep"></div>

                    <div class="network-node node-active" style="top: 25%; left: 30%;"
                        data-info="{{ __('ui.network_request_tech') }}">
                        <div class="node-dot"></div>
                        <div class="node-ripple"></div>
                    </div>

                    <div class="network-node node-active delay-1" style="top: 60%; left: 45%;"
                        data-info="{{ __('ui.network_new_motor_ad') }}">
                        <div class="node-dot dot-amber"></div>
                        <div class="node-ripple ripple-amber"></div>
                    </div>

                    <div class="network-node node-active delay-2" style="top: 40%; left: 75%;"
                        data-info="{{ __('ui.network_commission_completed') }}">
                        <div class="node-dot"></div>
                        <div class="node-ripple"></div>
                    </div>

                    <div class="network-node node-active delay-3" style="top: 75%; left: 20%;"
                        data-info="{{ __('ui.network_design_request') }}">
                        <div class="node-dot"></div>
                        <div class="node-ripple"></div>
                    </div>

                    <div class="radar-log-panel p-3 rounded font-monospace fs-8" id="radarLogPanel">
                        <div class="text-white-50 mb-1">// SYSTEM STATUS: ACTIVE</div>
                        <div class="text-neon-cyan" id="radarLogText">{{ __('ui.network_hover_hint') }}</div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</section>
