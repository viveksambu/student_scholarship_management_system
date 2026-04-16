document.addEventListener('DOMContentLoaded', () => {
    const categoryCards = document.querySelectorAll('.category-card');
    const resultsSection = document.getElementById('results-section');
    const loader = document.getElementById('loader');
    const scholarshipsGrid = document.getElementById('scholarships-grid');
    const noResults = document.getElementById('no-results');
    const resultsCount = document.getElementById('results-count');
    const currentStreamBadge = document.getElementById('current-stream-badge');
    const cardTemplate = document.getElementById('scholarship-card-template');

    let currentStudents = 0;
    let currentScholarships = 0;

    // Check query params for stream
    const urlParams = new URLSearchParams(window.location.search);
    const initialStream = urlParams.get('stream');
    if (initialStream) {
        setTimeout(() => {
            const btn = document.querySelector(`.category-card[data-stream='${initialStream}']`);
            if(btn) btn.click();
            else fetchScholarships(initialStream);
        }, 100);
    }

    // Fetch and animate statistics on load
    const fetchAndAnimateStats = async () => {
        try {
            const response = await fetch('stats.php');
            const result = await response.json();
            
            if(result.status === 'success') {
                if (currentStudents === 0) {
                    animateValue("stat-students", 0, result.data.total_students, 2500);
                } else if (result.data.total_students > currentStudents) {
                    animateValue("stat-students", currentStudents, result.data.total_students, 1000);
                }
                currentStudents = result.data.total_students;

                if (currentScholarships === 0) {
                    animateValue("stat-scholarships", 0, result.data.total_scholarships, 1500);
                } else if (result.data.total_scholarships !== currentScholarships) {
                    animateValue("stat-scholarships", currentScholarships, result.data.total_scholarships, 1000);
                }
                currentScholarships = result.data.total_scholarships;
            }
        } catch (error) {
            console.error('Failed to load stats:', error);
            if (currentStudents === 0) {
                document.getElementById("stat-students").textContent = "15,000+";
                document.getElementById("stat-scholarships").textContent = "500+";
            }
        }
    };

    // Logic for animating numbers up
    function animateValue(id, start, end, duration) {
        if (start === end) return;
        const obj = document.getElementById(id);
        if(!obj) return;
        let startTimestamp = null;
        const step = (timestamp) => {
            if (!startTimestamp) startTimestamp = timestamp;
            const progress = Math.min((timestamp - startTimestamp) / duration, 1);
            // using easeOut effect
            const easeOutProgress = 1 - Math.pow(1 - progress, 3);
            const currentObjValue = Math.floor(easeOutProgress * (end - start) + start);
            obj.innerHTML = currentObjValue.toLocaleString() + "+";
            if (progress < 1) {
                window.requestAnimationFrame(step);
            } else {
                obj.innerHTML = end.toLocaleString() + "+";
            }
        };
        window.requestAnimationFrame(step);
    }

    fetchAndAnimateStats();
    
    // Poll every 4 seconds to make students metric increment in real-time
    setInterval(fetchAndAnimateStats, 4000);

    // Fetch and display active ad
    const fetchAndDisplayAd = async () => {
        try {
            const response = await fetch('api_ads.php');
            const result = await response.json();
            const adContainer = document.getElementById('dynamic-ad-container');
            const adLink = document.getElementById('dynamic-ad-link');
            const adImg = document.getElementById('dynamic-ad-img');

            if (adContainer && adLink && adImg) {
                if (result.success && result.ad) {
                    adLink.href = result.ad.target_url;
                    adImg.src = result.ad.banner_image;
                    adContainer.style.display = 'block';
                } else {
                    adContainer.style.display = 'none';
                }
            }
        } catch (error) {
            console.error('Failed to load ad:', error);
        }
    };
    fetchAndDisplayAd();

    // Function to search scholarships
    const fetchScholarships = async (streamName) => {
        // UI Reset & Loading State
        resultsSection.style.display = 'block';
        scholarshipsGrid.innerHTML = '';
        noResults.style.display = 'none';
        loader.style.display = 'flex';
        scholarshipsGrid.style.display = 'none';
        
        currentStreamBadge.textContent = streamName;
        
        // Scroll down slightly so results are fully in view
        resultsSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        
        try {
            let url = `api.php?stream=${encodeURIComponent(streamName)}`;
            
            // Artificial delay for UI effect (premium feel of "searching")
            await new Promise(resolve => setTimeout(resolve, 600));

            const response = await fetch(url);
            
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            
            const result = await response.json();
            
            loader.style.display = 'none';
            scholarshipsGrid.style.display = 'grid';

            if (result.status === "success" && result.data && result.data.length > 0) {
                resultsCount.textContent = `${result.data.length} found`;
                renderScholarships(result.data);
            } else {
                resultsCount.textContent = `0 found`;
                scholarshipsGrid.style.display = 'none';
                noResults.style.display = 'flex';
            }
        } catch (error) {
            console.error('Error fetching scholarships:', error);
            loader.style.display = 'none';
            
            // Show error state
            noResults.querySelector('h3').textContent = 'Connection Error';
            noResults.querySelector('p').textContent = 'Please make sure your XAMPP server (Apache & MySQL) is running and the database is configured.';
            noResults.style.display = 'flex';
            resultsCount.textContent = `0 found`;
        }
    };

    // Format date beautifully
    const formatDate = (dateString) => {
        const options = { year: 'numeric', month: 'long', day: 'numeric' };
        return new Date(dateString).toLocaleDateString('en-US', options);
    };

    // Render cards
    const renderScholarships = (scholarships) => {
        scholarships.forEach((scholarship, index) => {
            const clone = cardTemplate.content.cloneNode(true);
            
            clone.querySelector('.provider-name').textContent = scholarship.provider;
            clone.querySelector('.amount-badge').textContent = scholarship.amount;
            clone.querySelector('.scholarship-title').textContent = scholarship.name;
            clone.querySelector('.scholarship-desc').innerHTML = scholarship.description;
            clone.querySelector('.deadline-date').textContent = formatDate(scholarship.deadline);
            
            // Event listener for opening modal
            const applyBtn = clone.querySelector('.apply-btn');
            applyBtn.addEventListener('click', () => {
                openScholarshipModal(scholarship);
            });
            
            // Stagger animations for cards
            const card = clone.querySelector('.scholarship-card');
            card.style.animation = `fadeInUp 0.5s ease ${index * 0.1}s both`;
            
            scholarshipsGrid.appendChild(clone);
        });
    };

    // Event listener for category cards
    categoryCards.forEach(card => {
        card.addEventListener('click', function() {
            // Remove active class from all
            categoryCards.forEach(c => c.classList.remove('active'));
            // Add active class to clicked
            this.classList.add('active');
            
            const stream = this.getAttribute('data-stream');
            fetchScholarships(stream);
        });
    });

    // Modal Logic
    const modalOverlay = document.getElementById('scholarship-modal');
    const modalClose = document.getElementById('modal-close');
    const modalBody = document.getElementById('modal-body');

    if(modalClose) {
        modalClose.addEventListener('click', () => {
            modalOverlay.style.display = 'none';
        });
    }

    function openScholarshipModal(scholarship) {
        modalBody.innerHTML = `
            <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                <div>
                    <h2 style="color: var(--primary); margin-bottom: 0.5rem; font-size: 1.8rem;">${scholarship.name}</h2>
                    <p style="color: var(--text-secondary); margin-bottom: 1.5rem; font-size: 1.1rem;">${scholarship.description}</p>
                </div>
            </div>
            
            <div class="details-title-bar">${scholarship.name} Key details</div>
            <table class="details-table">
                <tbody>
                    <tr>
                        <th>Particulars</th>
                        <th>Details</th>
                    </tr>
                    <tr>
                        <td>Name of the Scholarship</td>
                        <td>${scholarship.name}</td>
                    </tr>
                    <tr>
                        <td>Provider Name</td>
                        <td>${scholarship.provider}</td>
                    </tr>
                    <tr>
                        <td>Award Amount</td>
                        <td>${scholarship.amount}</td>
                    </tr>
                    <tr>
                        <td>Eligible Stream</td>
                        <td>${scholarship.stream}</td>
                    </tr>
                    <tr>
                        <td>Application Deadline</td>
                        <td>${formatDate(scholarship.deadline)}</td>
                    </tr>
                    <tr>
                        <td>Eligibility Criteria</td>
                        <td>${scholarship.eligibility || 'Not specified'}</td>
                    </tr>
                </tbody>
            </table>
            
            <div style="margin-top: 2rem; text-align: center;">
                <a href="${scholarship.apply_url || '#'}" target="_blank" class="apply-btn" style="text-decoration: none; display: inline-block;">Proceed to Apply Securely</a>
            </div>
        `;
        modalOverlay.style.display = 'flex';
    }
});
