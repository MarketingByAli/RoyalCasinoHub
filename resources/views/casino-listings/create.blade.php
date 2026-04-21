@extends('layouts.app')

@section('content')
<div style="max-width:960px;margin:0 auto">

    <nav style="margin-bottom:24px;font-size:14px" aria-label="Breadcrumb">
        <ol style="display:flex;flex-wrap:wrap;gap:8px;color:#6b7280;list-style:none;padding:0;margin:0">
            <li><a href="{{ route('home') }}" style="color:#6b7280;text-decoration:none;transition:color 0.2s" onmouseover="this.style.color='#fbbf24'" onmouseout="this.style.color='#6b7280'">Home</a></li>
            <li style="color:#4b5563">/</li>
            <li style="color:#fbbf24">Submit Listing</li>
        </ol>
    </nav>

    <div style="text-align:center;margin-bottom:48px">
        <div style="width:64px;height:64px;border-radius:20px;background:rgba(245,158,11,0.1);border:1px solid rgba(245,158,11,0.2);display:inline-flex;align-items:center;justify-content:center;margin-bottom:20px">
            <svg style="width:28px;height:28px;color:#fbbf24" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
        </div>
        <h1 style="font-size:clamp(1.75rem,4vw,2.25rem);font-weight:700;font-family:'Playfair Display',serif;color:#fff;margin:0 0 8px">Submit Your Casino</h1>
        <p style="color:#6b7280;font-size:15px;max-width:480px;margin:0 auto;line-height:1.6">Get your casino listed on the world's premier directory. Fill in the details below and our team will review your submission.</p>
    </div>

    @if($errors->any())
    <div style="background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.25);border-radius:14px;padding:16px 20px;margin-bottom:24px;display:flex;align-items:flex-start;gap:12px">
        <svg style="width:20px;height:20px;color:#f87171;flex-shrink:0;margin-top:1px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
        <div>
            <p style="color:#fca5a5;font-size:14px;font-weight:600;margin:0 0 4px">Please fix the following errors:</p>
            @foreach($errors->all() as $error)
                <p style="color:#f87171;font-size:13px;margin:2px 0">{{ $error }}</p>
            @endforeach
        </div>
    </div>
    @endif

    <div style="display:grid;grid-template-columns:1fr;gap:32px">
        @php $cols = '1fr 320px'; @endphp
        <div style="display:grid;grid-template-columns:1fr;gap:32px" class="lg-two-col">

            <div style="background:rgba(15,15,25,0.7);border:1px solid rgba(180,120,30,0.15);border-radius:20px;overflow:hidden">
                <div style="height:3px;background:linear-gradient(to right,rgba(245,158,11,0.4),rgba(217,119,6,0.2),rgba(245,158,11,0.4))"></div>

                <form method="POST" action="{{ route('casino-listings.store') }}" style="padding:32px">
                    @csrf

                    <div style="margin-bottom:32px">
                        <div style="display:flex;align-items:center;gap:10px;margin-bottom:20px">
                            <div style="width:32px;height:32px;border-radius:10px;background:linear-gradient(135deg,#f59e0b,#d97706);display:flex;align-items:center;justify-content:center;color:#451a03;font-weight:800;font-size:13px;flex-shrink:0">1</div>
                            <h2 style="font-size:16px;font-weight:700;color:#fff;margin:0">Casino Information</h2>
                        </div>

                        <div style="display:flex;flex-direction:column;gap:20px">
                            <div>
                                <label style="display:block;font-size:13px;color:#9ca3af;margin-bottom:6px;font-weight:500">Casino Name <span style="color:#f87171">*</span></label>
                                <div style="position:relative">
                                    <div style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#4b5563;display:flex;pointer-events:none">
                                        <svg style="width:18px;height:18px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                    </div>
                                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. Royal Ace Casino"
                                        style="width:100%;background:rgba(15,15,25,0.8);border:1px solid {{ $errors->has('name') ? 'rgba(239,68,68,0.5)' : 'rgba(180,120,30,0.2)' }};border-radius:12px;padding:14px 14px 14px 44px;color:#fff;font-size:14px;outline:none;transition:border-color 0.2s;box-sizing:border-box"
                                        onfocus="this.style.borderColor='rgba(245,158,11,0.5)'" onblur="this.style.borderColor='{{ $errors->has('name') ? 'rgba(239,68,68,0.5)' : 'rgba(180,120,30,0.2)' }}'">
                                </div>
                            </div>

                            <div>
                                <label style="display:block;font-size:13px;color:#9ca3af;margin-bottom:6px;font-weight:500">Website</label>
                                <div style="position:relative">
                                    <div style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#4b5563;display:flex;pointer-events:none">
                                        <svg style="width:18px;height:18px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                                    </div>
                                    <input type="text" name="website" value="{{ old('website') }}" placeholder="example.com or https://..."
                                        style="width:100%;background:rgba(15,15,25,0.8);border:1px solid {{ $errors->has('website') ? 'rgba(239,68,68,0.5)' : 'rgba(180,120,30,0.2)' }};border-radius:12px;padding:14px 14px 14px 44px;color:#fff;font-size:14px;outline:none;transition:border-color 0.2s;box-sizing:border-box"
                                        onfocus="this.style.borderColor='rgba(245,158,11,0.5)'" onblur="this.style.borderColor='{{ $errors->has('website') ? 'rgba(239,68,68,0.5)' : 'rgba(180,120,30,0.2)' }}'">
                                </div>
                            </div>

                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                                <div>
                                    <label style="display:block;font-size:13px;color:#9ca3af;margin-bottom:6px;font-weight:500">Founded Year</label>
                                    <div style="position:relative">
                                        <div style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#4b5563;display:flex;pointer-events:none">
                                            <svg style="width:18px;height:18px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        </div>
                                        <input type="text" name="founded" value="{{ old('founded') }}" placeholder="e.g. 2021" inputmode="numeric"
                                            style="width:100%;background:rgba(15,15,25,0.8);border:1px solid {{ $errors->has('founded') ? 'rgba(239,68,68,0.5)' : 'rgba(180,120,30,0.2)' }};border-radius:12px;padding:14px 14px 14px 44px;color:#fff;font-size:14px;outline:none;transition:border-color 0.2s;box-sizing:border-box"
                                            onfocus="this.style.borderColor='rgba(245,158,11,0.5)'" onblur="this.style.borderColor='{{ $errors->has('founded') ? 'rgba(239,68,68,0.5)' : 'rgba(180,120,30,0.2)' }}'">
                                    </div>
                                </div>
                                <div>
                                    <label style="display:block;font-size:13px;color:#9ca3af;margin-bottom:6px;font-weight:500">LinkedIn</label>
                                    <div style="position:relative">
                                        <div style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#4b5563;display:flex;pointer-events:none">
                                            <svg style="width:18px;height:18px" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                                        </div>
                                        <input type="text" name="linkedin" value="{{ old('linkedin') }}" placeholder="Company page URL"
                                            style="width:100%;background:rgba(15,15,25,0.8);border:1px solid {{ $errors->has('linkedin') ? 'rgba(239,68,68,0.5)' : 'rgba(180,120,30,0.2)' }};border-radius:12px;padding:14px 14px 14px 44px;color:#fff;font-size:14px;outline:none;transition:border-color 0.2s;box-sizing:border-box"
                                            onfocus="this.style.borderColor='rgba(245,158,11,0.5)'" onblur="this.style.borderColor='{{ $errors->has('linkedin') ? 'rgba(239,68,68,0.5)' : 'rgba(180,120,30,0.2)' }}'">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div style="width:100%;height:1px;background:linear-gradient(to right,transparent,rgba(180,120,30,0.15),transparent);margin-bottom:32px"></div>

                    <div style="margin-bottom:32px">
                        <div style="display:flex;align-items:center;gap:10px;margin-bottom:20px">
                            <div style="width:32px;height:32px;border-radius:10px;background:linear-gradient(135deg,#f59e0b,#d97706);display:flex;align-items:center;justify-content:center;color:#451a03;font-weight:800;font-size:13px;flex-shrink:0">2</div>
                            <h2 style="font-size:16px;font-weight:700;color:#fff;margin:0">Location Details</h2>
                        </div>

                        <div style="display:flex;flex-direction:column;gap:20px">
                            <div>
                                <label style="display:block;font-size:13px;color:#9ca3af;margin-bottom:6px;font-weight:500">Country <span style="color:#f87171">*</span></label>
                                <div style="position:relative">
                                    <div style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#4b5563;display:flex;pointer-events:none">
                                        <svg style="width:18px;height:18px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </div>
                                    <input type="text" name="country" value="{{ old('country') }}" required placeholder="e.g. United Kingdom"
                                        style="width:100%;background:rgba(15,15,25,0.8);border:1px solid {{ $errors->has('country') ? 'rgba(239,68,68,0.5)' : 'rgba(180,120,30,0.2)' }};border-radius:12px;padding:14px 14px 14px 44px;color:#fff;font-size:14px;outline:none;transition:border-color 0.2s;box-sizing:border-box"
                                        onfocus="this.style.borderColor='rgba(245,158,11,0.5)'" onblur="this.style.borderColor='{{ $errors->has('country') ? 'rgba(239,68,68,0.5)' : 'rgba(180,120,30,0.2)' }}'">
                                </div>
                            </div>

                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                                <div>
                                    <label style="display:block;font-size:13px;color:#9ca3af;margin-bottom:6px;font-weight:500">Region / State</label>
                                    <div style="position:relative">
                                        <div style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#4b5563;display:flex;pointer-events:none">
                                            <svg style="width:18px;height:18px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l5.447 2.724A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                                        </div>
                                        <input type="text" name="region" value="{{ old('region') }}" placeholder="e.g. England"
                                            style="width:100%;background:rgba(15,15,25,0.8);border:1px solid {{ $errors->has('region') ? 'rgba(239,68,68,0.5)' : 'rgba(180,120,30,0.2)' }};border-radius:12px;padding:14px 14px 14px 44px;color:#fff;font-size:14px;outline:none;transition:border-color 0.2s;box-sizing:border-box"
                                            onfocus="this.style.borderColor='rgba(245,158,11,0.5)'" onblur="this.style.borderColor='{{ $errors->has('region') ? 'rgba(239,68,68,0.5)' : 'rgba(180,120,30,0.2)' }}'">
                                    </div>
                                </div>
                                <div>
                                    <label style="display:block;font-size:13px;color:#9ca3af;margin-bottom:6px;font-weight:500">City / Locality</label>
                                    <div style="position:relative">
                                        <div style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#4b5563;display:flex;pointer-events:none">
                                            <svg style="width:18px;height:18px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        </div>
                                        <input type="text" name="locality" value="{{ old('locality') }}" placeholder="e.g. London"
                                            style="width:100%;background:rgba(15,15,25,0.8);border:1px solid {{ $errors->has('locality') ? 'rgba(239,68,68,0.5)' : 'rgba(180,120,30,0.2)' }};border-radius:12px;padding:14px 14px 14px 44px;color:#fff;font-size:14px;outline:none;transition:border-color 0.2s;box-sizing:border-box"
                                            onfocus="this.style.borderColor='rgba(245,158,11,0.5)'" onblur="this.style.borderColor='{{ $errors->has('locality') ? 'rgba(239,68,68,0.5)' : 'rgba(180,120,30,0.2)' }}'">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="submit"
                        style="width:100%;background:linear-gradient(135deg,#f59e0b,#d97706);color:#451a03;font-weight:700;padding:16px;border-radius:14px;border:none;font-size:15px;letter-spacing:0.02em;cursor:pointer;transition:all 0.2s;box-shadow:0 4px 20px rgba(245,158,11,0.25);display:flex;align-items:center;justify-content:center;gap:8px"
                        onmouseover="this.style.background='linear-gradient(135deg,#fbbf24,#f59e0b)';this.style.boxShadow='0 8px 30px rgba(245,158,11,0.35)';this.style.transform='translateY(-1px)'"
                        onmouseout="this.style.background='linear-gradient(135deg,#f59e0b,#d97706)';this.style.boxShadow='0 4px 20px rgba(245,158,11,0.25)';this.style.transform='translateY(0)'">
                        <svg style="width:20px;height:20px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                        Submit for Review
                    </button>
                </form>
            </div>

            <div style="display:flex;flex-direction:column;gap:20px">

                <div style="background:rgba(15,15,25,0.7);border:1px solid rgba(180,120,30,0.15);border-radius:16px;padding:24px">
                    <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px">
                        <div style="width:36px;height:36px;border-radius:10px;background:rgba(245,158,11,0.1);border:1px solid rgba(245,158,11,0.2);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                            <svg style="width:18px;height:18px;color:#fbbf24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <h3 style="font-size:15px;font-weight:700;color:#fff;margin:0">What Happens Next</h3>
                    </div>

                    @php
                    $steps = [
                        ['icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>', 'title' => 'Submit', 'desc' => 'Fill out the form with your casino details'],
                        ['icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>', 'title' => 'Review', 'desc' => 'Our team verifies your listing details'],
                        ['icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>', 'title' => 'Go Live', 'desc' => 'Approved casinos appear in the directory'],
                    ];
                    @endphp

                    <div style="display:flex;flex-direction:column;gap:0">
                        @foreach($steps as $i => $step)
                        <div style="display:flex;gap:14px;position:relative">
                            @if(!$loop->last)
                            <div style="position:absolute;left:15px;top:32px;bottom:0;width:1px;background:linear-gradient(to bottom,rgba(245,158,11,0.3),rgba(180,120,30,0.1))" aria-hidden="true"></div>
                            @endif
                            <div style="width:32px;height:32px;border-radius:50%;background:rgba(245,158,11,0.1);border:1px solid rgba(245,158,11,0.2);display:flex;align-items:center;justify-content:center;flex-shrink:0;position:relative;z-index:1">
                                <svg style="width:14px;height:14px;color:#fbbf24" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $step['icon'] !!}</svg>
                            </div>
                            <div style="padding-bottom:{{ $loop->last ? '0' : '20px' }}">
                                <div style="font-size:13px;font-weight:600;color:#e5e7eb;margin-bottom:2px">{{ $step['title'] }}</div>
                                <div style="font-size:12px;color:#6b7280;line-height:1.5">{{ $step['desc'] }}</div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div style="background:rgba(15,15,25,0.7);border:1px solid rgba(180,120,30,0.15);border-radius:16px;padding:24px">
                    <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px">
                        <div style="width:36px;height:36px;border-radius:10px;background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.2);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                            <svg style="width:18px;height:18px;color:#34d399" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        </div>
                        <h3 style="font-size:15px;font-weight:700;color:#fff;margin:0">Listing Benefits</h3>
                    </div>

                    <div style="display:flex;flex-direction:column;gap:12px">
                        @foreach(['Exposure to thousands of casino seekers', 'Verified badge once approved', 'Appear in country-specific searches', 'Collect player reviews & ratings', 'Owner dashboard with analytics'] as $benefit)
                        <div style="display:flex;align-items:center;gap:10px">
                            <svg style="width:16px;height:16px;color:#34d399;flex-shrink:0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span style="font-size:13px;color:#9ca3af">{{ $benefit }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div style="background:rgba(245,158,11,0.04);border:1px solid rgba(245,158,11,0.12);border-radius:16px;padding:20px;display:flex;gap:12px;align-items:flex-start">
                    <svg style="width:18px;height:18px;color:#fbbf24;flex-shrink:0;margin-top:1px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                    <p style="font-size:12px;color:#9ca3af;line-height:1.6;margin:0">Listings remain <span style="color:#e5e7eb;font-weight:600">pending</span> until admin review. You must verify your email before submitting.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @media (min-width: 1024px) {
        .lg-two-col {
            grid-template-columns: 1fr 320px !important;
        }
    }
</style>
@endsection
